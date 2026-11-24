<?php

namespace App\Services\Plugin;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CoreShortcodeRegistrar
{
    public function register(ShortcodeManager $shortcodes): void
    {
        $shortcodes->register('button', fn (array $attrs) => $this->button($attrs));
        $shortcodes->register('zalo_chat', fn (array $attrs) => $this->zaloChat($attrs));
        $shortcodes->register('map', fn (array $attrs) => $this->map($attrs));
        $shortcodes->register('contact_form', fn (array $attrs) => $this->contactForm($attrs));
        $shortcodes->register('lead_form', fn (array $attrs) => $this->contactForm($attrs, true));
        $shortcodes->register('product_list', fn (array $attrs) => $this->productList($attrs));
        $shortcodes->register('gallery', fn (array $attrs) => $this->gallery($attrs));
        $shortcodes->register('pricing_table', fn (array $attrs) => $this->placeholder('pricing_table', $attrs));
    }

    private function button(array $attrs): string
    {
        $text = $attrs['text'] ?? 'Xem thêm';
        $link = $attrs['link'] ?? $attrs['url'] ?? '#';
        $style = $attrs['style'] ?? 'primary';
        $style = in_array($style, ['primary', 'outline'], true) ? $style : 'primary';
        $target = ($attrs['target'] ?? '') === '_blank' ? ' target="_blank" rel="noopener"' : '';

        return sprintf(
            '<a class="btn btn-%s cms-shortcode-button" href="%s"%s>%s</a>',
            $style === 'outline' ? 'outline-dark' : 'primary',
            e($link),
            $target,
            e($text),
        );
    }

    private function zaloChat(array $attrs): string
    {
        $phone = preg_replace('/\D+/', '', $attrs['phone'] ?? Setting::get('zalo', Setting::get('hotline', '')));
        $text = $attrs['text'] ?? 'Chat Zalo';

        if ($phone === '') {
            return '';
        }

        return sprintf(
            '<a class="btn btn-outline-dark cms-shortcode-zalo" href="https://zalo.me/%s" target="_blank" rel="noopener">%s</a>',
            e($phone),
            e($text),
        );
    }

    private function map(array $attrs): string
    {
        $address = trim($attrs['address'] ?? Setting::get('address', ''));
        if ($address === '') {
            return '';
        }

        $height = max(180, min(720, (int) ($attrs['height'] ?? 360)));

        return sprintf(
            '<iframe class="cms-shortcode-map" title="%s" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=%s&output=embed" style="width:100%%;height:%dpx;border:0;border-radius:8px"></iframe>',
            e($address),
            rawurlencode($address),
            $height,
        );
    }

    private function contactForm(array $attrs, bool $isLeadForm = false): string
    {
        $source = $attrs['source'] ?? ($isLeadForm ? 'lead-form-shortcode' : 'contact-form-shortcode');
        $title = $attrs['title'] ?? ($isLeadForm ? 'Nhận tư vấn' : 'Gửi thông tin liên hệ');
        $button = $attrs['button'] ?? ($isLeadForm ? 'Gửi yêu cầu' : 'Gửi liên hệ');

        return view('shortcodes.contact-form', [
            'title' => $title,
            'button' => $button,
            'source' => $source,
        ])->render();
    }

    private function productList(array $attrs): string
    {
        $limit = max(1, min(24, (int) ($attrs['limit'] ?? 6)));
        $category = trim($attrs['category'] ?? '');

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($category !== '', function ($query) use ($category) {
                $query->whereHas('category', function ($categoryQuery) use ($category) {
                    $categoryQuery->where('slug', $category)->orWhere('name', $category);
                });
            })
            ->latest()
            ->limit($limit)
            ->get();

        if ($products->isEmpty()) {
            return '';
        }

        return view('shortcodes.product-list', [
            'products' => $products,
            'title' => $attrs['title'] ?? null,
        ])->render();
    }

    private function gallery(array $attrs): string
    {
        $images = $this->galleryImages($attrs);
        if ($images === []) {
            return '';
        }

        return view('shortcodes.gallery', ['images' => $images])->render();
    }

    private function galleryImages(array $attrs): array
    {
        $rawImages = trim($attrs['images'] ?? '');
        if ($rawImages !== '') {
            return collect(explode(',', $rawImages))
                ->map(fn ($image) => trim($image))
                ->filter()
                ->map(fn ($image) => $this->imageUrl($image))
                ->values()
                ->all();
        }

        $id = (int) ($attrs['id'] ?? 0);
        if ($id <= 0) {
            return [];
        }

        $product = Product::find($id);
        if (! $product || ! $product->gallery) {
            return [];
        }

        $decoded = json_decode($product->gallery, true);
        $items = is_array($decoded) ? $decoded : explode(',', $product->gallery);

        return collect(Arr::wrap($items))
            ->map(fn ($image) => is_array($image) ? ($image['url'] ?? $image['path'] ?? '') : (string) $image)
            ->map(fn ($image) => trim($image))
            ->filter()
            ->map(fn ($image) => $this->imageUrl($image))
            ->values()
            ->all();
    }

    private function imageUrl(string $image): string
    {
        if (Str::startsWith($image, ['http://', 'https://', '/'])) {
            return $image;
        }

        if (Str::startsWith($image, 'theme-assets/')) {
            return asset($image);
        }

        return asset('storage/'.$image);
    }

    private function placeholder(string $tag, array $attrs): string
    {
        if (app()->environment('production')) {
            return '';
        }

        $id = $attrs['id'] ?? '';

        return '<div class="cms-shortcode-placeholder">'.e("[{$tag}".($id !== '' ? " id=\"{$id}\"" : '').']').'</div>';
    }
}
