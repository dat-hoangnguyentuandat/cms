<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Category;
use App\Models\HomepageBlock;
use App\Services\Theme\ThemeManager;
use App\Services\Widget\WidgetRegistry;
use App\Services\Widget\WidgetSettingsSchemaValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomepageLayoutController extends Controller
{
    public function __construct(
        private ThemeManager $themeManager,
        private WidgetRegistry $registry,
        private WidgetSettingsSchemaValidator $schemaValidator,
    ) {}

    public function index()
    {
        $activeTheme = $this->themeManager->active();
        $this->registry->load($activeTheme);
        $this->ensureThemeDefaults($activeTheme);

        $blocksByZone = HomepageBlock::where('theme_name', $activeTheme)->orderBy('sort_order')->get()->groupBy('zone');
        $zones = $this->registry->zones();
        $blockTypes = $this->registry->blockTypes();
        $allowedZonesMap = $this->registry->allowedZonesMap();
        $widgetVariants = $this->scanWidgetVariants($blockTypes);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.homepage-layout.index', compact(
            'blocksByZone', 'zones', 'blockTypes', 'allowedZonesMap', 'activeTheme', 'widgetVariants', 'categories'
        ));
    }

    public function store(Request $request)
    {
        $activeTheme = $this->themeManager->active();
        $this->registry->load($activeTheme);

        $data = $this->validated($request);
        $data['theme_name'] = $activeTheme;
        $maxOrder = (int) HomepageBlock::where('theme_name', $activeTheme)->where('zone', $data['zone'])->max('sort_order');
        $insertAfterId = (int) $request->input('insert_after_id', 0);
        $insertBeforeId = (int) $request->input('insert_before_id', 0);
        $anchorQuery = HomepageBlock::where('theme_name', $activeTheme)->where('zone', $data['zone']);
        $afterBlock = $insertAfterId ? (clone $anchorQuery)->where('id', $insertAfterId)->first() : null;
        $beforeBlock = $insertBeforeId ? (clone $anchorQuery)->where('id', $insertBeforeId)->first() : null;

        if ($afterBlock) {
            $data['sort_order'] = $afterBlock->sort_order + 1;
        } elseif ($beforeBlock) {
            $data['sort_order'] = $beforeBlock->sort_order;
        } else {
            $requestedOrder = (int) $request->input('sort_order', 0);
            $data['sort_order'] = $requestedOrder > 0 ? min($requestedOrder, $maxOrder + 1) : $maxOrder + 1;
        }

        HomepageBlock::where('theme_name', $activeTheme)
            ->where('zone', $data['zone'])
            ->where('sort_order', '>=', $data['sort_order'])
            ->increment('sort_order');
        $data['is_active'] = true;
        $data['settings'] = $this->validatedSettings($request, $data['block_type'], [], $data['style_key'] ?? '');

        HomepageBlock::create($data);

        return redirect()->route('admin.homepage-layout.index')->with('success', 'Đã thêm khối nội dung.');
    }

    public function update(Request $request, HomepageBlock $homepageBlock)
    {
        $this->registry->load($this->themeManager->active());

        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['settings'] = $this->validatedSettings($request, $data['block_type'], $homepageBlock->settings ?? [], $data['style_key'] ?? $homepageBlock->style_key ?? '');
        $homepageBlock->update($data);

        $this->syncSortOrders($homepageBlock->zone);

        return redirect()->route('admin.homepage-layout.index')->with('success', 'Đã cập nhật khối nội dung.');
    }

    public function destroy(HomepageBlock $homepageBlock)
    {
        $zone = $homepageBlock->zone;
        $homepageBlock->delete();
        $this->syncSortOrders($zone);

        return redirect()->route('admin.homepage-layout.index')->with('success', 'Đã xóa khối nội dung.');
    }

    public function reorder(Request $request)
    {
        $activeTheme = $this->themeManager->active();
        $this->registry->load($activeTheme);
        $validZones = array_keys($this->registry->zones());

        foreach ($request->input('zones', []) as $zone => $blockIds) {
            if (! in_array($zone, $validZones)) {
                continue;
            }
            foreach ($blockIds as $order => $id) {
                HomepageBlock::where('theme_name', $activeTheme)->where('id', $id)->update([
                    'zone' => $zone,
                    'sort_order' => $order + 1,
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function validated(Request $request): array
    {
        $validZones = implode(',', array_keys($this->registry->zones()));
        $validTypes = implode(',', array_keys($this->registry->widgets()));

        return $request->validate([
            'zone' => 'required|in:'.$validZones,
            'block_type' => 'required|in:'.$validTypes,
            'style_key' => 'nullable|max:80',
            'title' => 'nullable|max:255',
        ]);
    }

    private function syncSortOrders(string $zone): void
    {
        HomepageBlock::where('theme_name', $this->themeManager->active())
            ->where('zone', $zone)
            ->orderBy('sort_order')
            ->get()
            ->each(fn ($block, $index) => $block->update(['sort_order' => $index + 1]));
    }

    private function validatedSettings(Request $request, string $blockType, array $existing = [], string $styleKey = ''): array
    {
        if ($this->schemaValidator->hasSchema($blockType)) {
            return $this->schemaValidator->validate($request, $blockType, $existing);
        }

        if (in_array($blockType, ['product-list', 'list-of-articles-by-category'])) {
            $settings = $request->validate([
                'settings.title_url' => 'nullable|string|max:255',
                'settings.subtitle' => 'nullable|string|max:1000',
                'settings.category_ids' => 'nullable|array',
                'settings.category_ids.*' => 'integer|exists:categories,id',
                'settings.show_children' => 'nullable|boolean',
                'settings.group_by_category' => 'nullable|boolean',
                'settings.show_all_tab' => 'nullable|boolean',
                'settings.limit_per_category' => 'nullable|integer|min:1|max:30',
                'settings.autoplay' => 'nullable|boolean',
                'settings.desktop_items' => ['nullable', Rule::in(['1', '2', '3', '4', '5', '6'])],
                'settings.mobile_items' => ['nullable', Rule::in(['1', '2', '3'])],
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            return [
                'title_url' => $settings['title_url'] ?? '',
                'subtitle' => $settings['subtitle'] ?? '',
                'category_ids' => array_values($settings['category_ids'] ?? []),
                'show_children' => (bool) ($settings['show_children'] ?? false),
                'group_by_category' => (bool) ($settings['group_by_category'] ?? false),
                'show_all_tab' => (bool) ($settings['show_all_tab'] ?? false),
                'limit_per_category' => (int) ($settings['limit_per_category'] ?? 6),
                'autoplay' => (bool) ($settings['autoplay'] ?? false),
                'desktop_items' => (int) ($settings['desktop_items'] ?? 4),
                'mobile_items' => (int) ($settings['mobile_items'] ?? 2),
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if ($blockType === 'banner' && $styleKey === 'banner_8') {
            $settings = $request->validate([
                'settings.title_url' => 'nullable|string|max:255',
                'settings.subtitle' => 'nullable|string|max:1000',
                'settings.title_size' => 'nullable|integer|min:10|max:72',
                'settings.title_align' => ['nullable', Rule::in(['left', 'center', 'right'])],
                'settings.width' => 'nullable|integer|min:0|max:5000',
                'settings.height' => 'nullable|integer|min:0|max:5000',
                'settings.content_source' => ['nullable', Rule::in(['manual', 'import'])],
                'settings.source_type' => ['nullable', Rule::in(['product', 'service', 'news'])],
                'settings.source_limit' => 'nullable|integer|min:1|max:6',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            $images = $existing['images'] ?? array_fill(0, 6, null);
            for ($i = 0; $i < 6; $i++) {
                if ($request->hasFile("settings.images.$i")) {
                    $images[$i] = $request->file("settings.images.$i")->store('homepage-banners', 'public');
                }
            }

            return [
                'title_url' => $settings['title_url'] ?? '',
                'subtitle' => $settings['subtitle'] ?? '',
                'title_size' => (int) ($settings['title_size'] ?? 16),
                'title_align' => $settings['title_align'] ?? 'center',
                'images' => $images,
                'content_source' => $settings['content_source'] ?? 'manual',
                'source_type' => $settings['source_type'] ?? 'product',
                'source_limit' => (int) ($settings['source_limit'] ?? 6),
                'width' => (int) ($settings['width'] ?? 0),
                'height' => (int) ($settings['height'] ?? 0),
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if ($blockType === 'service-block') {
            $settings = $request->validate([
                'settings.title' => 'nullable|string|max:255',
                'settings.title_url' => 'nullable|string|max:255',
                'settings.heading_size' => 'nullable|integer|min:10|max:72',
                'settings.heading_color' => 'nullable|string|max:20',
                'settings.subtitle' => 'nullable|string|max:1000',
                'settings.layout' => ['nullable', Rule::in(['image_top', 'image_card', 'product_cards', 'icons_only', 'icon_left', 'icon_card', 'icon_line', 'number_icon', 'image_side', 'image_diamond', 'overlay', 'color_blocks', 'center_image_features', 'simple_horizontal'])],
                'settings.icon_position' => ['nullable', Rule::in(['top', 'line'])],
                'settings.image_position' => ['nullable', Rule::in(['left', 'right'])],
                'settings.icon_shape' => ['nullable', Rule::in(['circle', 'square', 'none'])],
                'settings.desktop_items' => ['nullable', Rule::in(['1', '2', '3', '4', '5', '6'])],
                'settings.mobile_items' => ['nullable', Rule::in(['1', '2', '3'])],
                'settings.icon_color' => 'nullable|string|max:20',
                'settings.title_color' => 'nullable|string|max:20',
                'settings.text_color' => 'nullable|string|max:20',
                'settings.background_color' => 'nullable|string|max:20',
                'settings.line_color' => 'nullable|string|max:20',
                'settings.content_source' => ['nullable', Rule::in(['manual', 'import'])],
                'settings.source_type' => ['nullable', Rule::in(['product', 'service', 'news'])],
                'settings.source_limit' => 'nullable|integer|min:1|max:12',
                'settings.side_image' => 'nullable|image|max:4096',
                'settings.items' => 'nullable|array',
                'settings.items.*.title' => 'nullable|string|max:255',
                'settings.items.*.url' => 'nullable|string|max:255',
                'settings.items.*.description' => 'nullable|string|max:500',
                'settings.items.*.icon' => 'nullable|string|max:100',
                'settings.items.*.existing_image' => 'nullable|string|max:255',
                'settings.items.*.existing_icon_image' => 'nullable|string|max:255',
                'settings.items.*.image' => 'nullable|image|max:4096',
                'settings.items.*.icon_image' => 'nullable|image|max:4096',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            $items = [];
            foreach (($settings['items'] ?? []) as $index => $item) {
                $image = $item['existing_image'] ?? null;
                $iconImage = $item['existing_icon_image'] ?? null;
                if ($request->hasFile("settings.items.$index.image")) {
                    $image = $request->file("settings.items.$index.image")->store('homepage-service-blocks', 'public');
                }
                if ($request->hasFile("settings.items.$index.icon_image")) {
                    $iconImage = $request->file("settings.items.$index.icon_image")->store('homepage-service-blocks', 'public');
                }
                $items[] = [
                    'title' => $item['title'] ?? '',
                    'url' => $item['url'] ?? '',
                    'description' => $item['description'] ?? '',
                    'icon' => $item['icon'] ?? 'fa fa-star',
                    'image' => $image,
                    'icon_image' => $iconImage,
                ];
            }

            $sideImage = $existing['side_image'] ?? null;
            if ($request->hasFile('settings.side_image')) {
                $sideImage = $request->file('settings.side_image')->store('homepage-service-blocks', 'public');
            }

            return [
                'title' => $settings['title'] ?? '',
                'title_url' => $settings['title_url'] ?? '',
                'heading_size' => (int) ($settings['heading_size'] ?? 18),
                'heading_color' => $settings['heading_color'] ?? ($settings['icon_color'] ?? '#1683f7'),
                'subtitle' => $settings['subtitle'] ?? '',
                'layout' => $settings['layout'] ?? 'image_top',
                'icon_position' => $settings['icon_position'] ?? 'top',
                'image_position' => $settings['image_position'] ?? 'right',
                'icon_shape' => $settings['icon_shape'] ?? 'circle',
                'desktop_items' => (int) ($settings['desktop_items'] ?? 3),
                'mobile_items' => (int) ($settings['mobile_items'] ?? 1),
                'icon_color' => $settings['icon_color'] ?? '#1683f7',
                'title_color' => $settings['title_color'] ?? '#1f2937',
                'text_color' => $settings['text_color'] ?? '#6b7280',
                'background_color' => $settings['background_color'] ?? '#ffffff',
                'line_color' => $settings['line_color'] ?? '#cfe3ff',
                'content_source' => $settings['content_source'] ?? 'manual',
                'source_type' => $settings['source_type'] ?? 'service',
                'source_limit' => (int) ($settings['source_limit'] ?? 6),
                'side_image' => $sideImage,
                'items' => $items,
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if ($blockType === 'partners') {
            $settings = $request->validate([
                'settings.title_url' => 'nullable|string|max:255',
                'settings.subtitle' => 'nullable|string|max:255',
                'settings.layout' => ['nullable', Rule::in(['marquee', 'grid'])],
                'settings.desktop_cols' => ['nullable', Rule::in(['2', '3', '4', '5', '6'])],
                'settings.speed' => 'nullable|integer|min:10|max:100',
                'settings.bg_color' => 'nullable|string|max:20',
                'settings.autoplay' => 'nullable|boolean',
                'settings.show_name' => 'nullable|boolean',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
                'settings.items' => 'nullable|array',
                'settings.items.*.name' => 'nullable|string|max:255',
                'settings.items.*.url' => 'nullable|string|max:255',
                'settings.items.*.existing_logo' => 'nullable|string|max:255',
                'settings.items.*.logo' => 'nullable|image|max:2048',
            ])['settings'] ?? [];

            $items = [];
            foreach (($settings['items'] ?? []) as $index => $item) {
                $logo = $item['existing_logo'] ?? null;
                if ($request->hasFile("settings.items.$index.logo")) {
                    $logo = $request->file("settings.items.$index.logo")->store('homepage-partners', 'public');
                }
                if (! $logo) {
                    continue;
                }
                $items[] = [
                    'name' => $item['name'] ?? '',
                    'url' => $item['url'] ?? '',
                    'logo' => $logo,
                ];
            }

            return [
                'title_url' => $settings['title_url'] ?? '',
                'subtitle' => $settings['subtitle'] ?? '',
                'layout' => $settings['layout'] ?? 'marquee',
                'desktop_cols' => (int) ($settings['desktop_cols'] ?? 5),
                'speed' => (int) ($settings['speed'] ?? 40),
                'bg_color' => $settings['bg_color'] ?? '#ffffff',
                'autoplay' => (bool) ($settings['autoplay'] ?? true),
                'show_name' => (bool) ($settings['show_name'] ?? false),
                'display_mode' => $settings['display_mode'] ?? 'show',
                'items' => $items,
            ];
        }

        if ($blockType === 'customer-testimonials') {
            $settings = $request->validate([
                'settings.title_url' => 'nullable|string|max:255',
                'settings.subtitle' => 'nullable|string|max:500',
                'settings.layout' => ['nullable', Rule::in(['slider', 'grid', 'card_minimal', 'bubble', 'centered', 'compact', 'featured'])],
                'settings.desktop_cols' => ['nullable', Rule::in(['1', '2', '3', '4'])],
                'settings.mobile_cols' => ['nullable', Rule::in(['1', '2'])],
                'settings.max_width' => 'nullable|integer|min:400|max:1600',
                'settings.section_padding' => 'nullable|integer|min:0|max:200',
                'settings.gap' => 'nullable|integer|min:0|max:80',
                'settings.bg_color' => 'nullable|string|max:20',
                'settings.card_bg' => 'nullable|string|max:20',
                'settings.accent_color' => 'nullable|string|max:20',
                'settings.title_color' => 'nullable|string|max:20',
                'settings.text_color' => 'nullable|string|max:20',
                'settings.heading_size' => 'nullable|integer|min:14|max:60',
                'settings.title_size' => 'nullable|integer|min:10|max:32',
                'settings.content_size' => 'nullable|integer|min:10|max:24',
                'settings.content_lines' => 'nullable|integer|min:0|max:30',
                'settings.avatar_size' => 'nullable|integer|min:32|max:160',
                'settings.card_radius' => 'nullable|integer|min:0|max:48',
                'settings.card_padding' => 'nullable|integer|min:8|max:60',
                'settings.star_position' => ['nullable', Rule::in(['top', 'after_name', 'before_content', 'bottom', 'hide'])],
                'settings.autoplay' => 'nullable|boolean',
                'settings.autoplay_speed' => 'nullable|integer|min:1|max:20',
                'settings.show_role' => 'nullable|boolean',
                'settings.show_company' => 'nullable|boolean',
                'settings.show_quote' => 'nullable|boolean',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
                'settings.content_source' => ['nullable', Rule::in(['manual', 'import'])],
                'settings.source_limit' => 'nullable|integer|min:1|max:50',
                'settings.items' => 'nullable|array',
                'settings.items.*.name' => 'nullable|string|max:255',
                'settings.items.*.role' => 'nullable|string|max:255',
                'settings.items.*.company' => 'nullable|string|max:255',
                'settings.items.*.content' => 'nullable|string|max:2000',
                'settings.items.*.rating' => 'nullable|integer|min:1|max:5',
                'settings.items.*.existing_avatar' => 'nullable|string|max:255',
                'settings.items.*.avatar' => 'nullable|image|max:2048',
            ])['settings'] ?? [];

            $items = [];
            foreach (($settings['items'] ?? []) as $index => $item) {
                $avatar = $item['existing_avatar'] ?? null;
                if ($request->hasFile("settings.items.$index.avatar")) {
                    $avatar = $request->file("settings.items.$index.avatar")->store('homepage-testimonials', 'public');
                }
                $items[] = [
                    'name' => $item['name'] ?? '',
                    'role' => $item['role'] ?? '',
                    'company' => $item['company'] ?? '',
                    'content' => $item['content'] ?? '',
                    'rating' => (int) ($item['rating'] ?? 5),
                    'avatar' => $avatar,
                ];
            }

            return [
                'title_url' => $settings['title_url'] ?? '',
                'subtitle' => $settings['subtitle'] ?? '',
                'layout' => $settings['layout'] ?? 'slider',
                'desktop_cols' => (int) ($settings['desktop_cols'] ?? 3),
                'mobile_cols' => (int) ($settings['mobile_cols'] ?? 1),
                'max_width' => (int) ($settings['max_width'] ?? 960),
                'section_padding' => (int) ($settings['section_padding'] ?? 64),
                'gap' => (int) ($settings['gap'] ?? 20),
                'bg_color' => $settings['bg_color'] ?? '#f8fafc',
                'card_bg' => $settings['card_bg'] ?? '#ffffff',
                'accent_color' => $settings['accent_color'] ?? '#1683f7',
                'title_color' => $settings['title_color'] ?? '#1a2340',
                'text_color' => $settings['text_color'] ?? '#4b5563',
                'heading_size' => (int) ($settings['heading_size'] ?? 30),
                'title_size' => (int) ($settings['title_size'] ?? 15),
                'content_size' => (int) ($settings['content_size'] ?? 14),
                'content_lines' => (int) ($settings['content_lines'] ?? 6),
                'avatar_size' => (int) ($settings['avatar_size'] ?? 80),
                'card_radius' => (int) ($settings['card_radius'] ?? 16),
                'card_padding' => (int) ($settings['card_padding'] ?? 28),
                'star_position' => $settings['star_position'] ?? 'before_content',
                'autoplay' => (bool) ($settings['autoplay'] ?? false),
                'autoplay_speed' => (int) ($settings['autoplay_speed'] ?? 5),
                'show_role' => (bool) ($settings['show_role'] ?? true),
                'show_company' => (bool) ($settings['show_company'] ?? true),
                'show_quote' => (bool) ($settings['show_quote'] ?? false),
                'display_mode' => $settings['display_mode'] ?? 'show',
                'content_source' => $settings['content_source'] ?? 'manual',
                'source_limit' => (int) ($settings['source_limit'] ?? 6),
                'items' => $items,
            ];
        }

        if ($blockType === 'contact-form') {
            $settings = $request->validate([
                'settings.title_url' => 'nullable|string|max:255',
                'settings.subtitle' => 'nullable|string|max:500',
                'settings.subject' => 'nullable|string|max:255',
                'settings.btn_text' => 'nullable|string|max:100',
                'settings.layout' => ['nullable', Rule::in(['two_col', 'form_only', 'form_center'])],
                'settings.bg_color' => 'nullable|string|max:20',
                'settings.accent_color' => 'nullable|string|max:20',
                'settings.show_phone' => 'nullable|boolean',
                'settings.show_email' => 'nullable|boolean',
                'settings.show_address' => 'nullable|boolean',
                'settings.req_phone' => 'nullable|boolean',
                'settings.req_email' => 'nullable|boolean',
                'settings.show_company' => 'nullable|boolean',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            return [
                'title_url' => $settings['title_url'] ?? '',
                'subtitle' => $settings['subtitle'] ?? '',
                'subject' => $settings['subject'] ?? 'Liên hệ từ trang chủ',
                'btn_text' => $settings['btn_text'] ?? 'Gửi liên hệ',
                'layout' => $settings['layout'] ?? 'two_col',
                'bg_color' => $settings['bg_color'] ?? '#f8fafc',
                'accent_color' => $settings['accent_color'] ?? '#1683f7',
                'show_phone' => (bool) ($settings['show_phone'] ?? true),
                'show_email' => (bool) ($settings['show_email'] ?? true),
                'show_address' => (bool) ($settings['show_address'] ?? true),
                'req_phone' => (bool) ($settings['req_phone'] ?? true),
                'req_email' => (bool) ($settings['req_email'] ?? false),
                'show_company' => (bool) ($settings['show_company'] ?? false),
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if ($blockType === 'floating-action-buttons') {
            $settings = $request->validate([
                'settings.position' => ['nullable', Rule::in(['top', 'middle', 'bottom'])],
            ])['settings'] ?? [];

            return [
                'position' => $settings['position'] ?? 'middle',
            ];
        }

        if ($blockType === 'stats') {
            $settings = $request->validate([
                'settings.subtitle' => 'nullable|string|max:500',
                'settings.background_image' => 'nullable|image|max:4096',
                'settings.items' => 'nullable|array',
                'settings.items.*.num' => 'nullable|string|max:30',
                'settings.items.*.label' => 'nullable|string|max:120',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            $background = $existing['background_image'] ?? null;
            if ($request->hasFile('settings.background_image')) {
                $background = $request->file('settings.background_image')->store('homepage-stats', 'public');
            }

            $items = [];
            foreach (($settings['items'] ?? []) as $item) {
                if (($item['num'] ?? '') === '' && ($item['label'] ?? '') === '') {
                    continue;
                }
                $items[] = ['num' => $item['num'] ?? '', 'label' => $item['label'] ?? ''];
            }

            return [
                'subtitle' => $settings['subtitle'] ?? '',
                'background_image' => $background,
                'items' => $items,
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if ($blockType === 'process') {
            $settings = $request->validate([
                'settings.subtitle' => 'nullable|string|max:500',
                'settings.items' => 'nullable|array',
                'settings.items.*.num' => 'nullable|string|max:10',
                'settings.items.*.title' => 'nullable|string|max:120',
                'settings.items.*.desc' => 'nullable|string|max:500',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            $items = [];
            foreach (($settings['items'] ?? []) as $i => $item) {
                if (($item['title'] ?? '') === '' && ($item['desc'] ?? '') === '') {
                    continue;
                }
                $items[] = [
                    'num' => $item['num'] !== null && $item['num'] !== '' ? $item['num'] : str_pad((string) (count($items) + 1), 2, '0', STR_PAD_LEFT),
                    'title' => $item['title'] ?? '',
                    'desc' => $item['desc'] ?? '',
                ];
            }

            return [
                'subtitle' => $settings['subtitle'] ?? '',
                'items' => $items,
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if ($blockType === 'why-choose') {
            $settings = $request->validate([
                'settings.intro_title' => 'nullable|string|max:255',
                'settings.intro_desc' => 'nullable|string|max:1000',
                'settings.btn_text' => 'nullable|string|max:100',
                'settings.btn_url' => 'nullable|string|max:255',
                'settings.items' => 'nullable|array',
                'settings.items.*.title' => 'nullable|string|max:120',
                'settings.items.*.desc' => 'nullable|string|max:500',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            $items = [];
            foreach (($settings['items'] ?? []) as $item) {
                if (($item['title'] ?? '') === '' && ($item['desc'] ?? '') === '') {
                    continue;
                }
                $items[] = ['title' => $item['title'] ?? '', 'desc' => $item['desc'] ?? ''];
            }

            return [
                'intro_title' => $settings['intro_title'] ?? '',
                'intro_desc' => $settings['intro_desc'] ?? '',
                'btn_text' => $settings['btn_text'] ?? '',
                'btn_url' => $settings['btn_url'] ?? '',
                'items' => $items,
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if ($blockType === 'newsletter') {
            $settings = $request->validate([
                'settings.subtitle' => 'nullable|string|max:1000',
                'settings.btn_text' => 'nullable|string|max:100',
                'settings.subject' => 'nullable|string|max:255',
                'settings.items' => 'nullable|array',
                'settings.items.*.text' => 'nullable|string|max:120',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            $items = [];
            foreach (($settings['items'] ?? []) as $item) {
                if (($item['text'] ?? '') === '') {
                    continue;
                }
                $items[] = ['text' => $item['text']];
            }

            return [
                'subtitle' => $settings['subtitle'] ?? '',
                'btn_text' => $settings['btn_text'] ?? '',
                'subject' => $settings['subject'] ?? '',
                'items' => $items,
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if ($blockType === 'slider') {
            $settings = $request->validate([
                'settings.autoplay' => 'nullable|boolean',
                'settings.items' => 'nullable|array',
                'settings.items.*.heading' => 'nullable|string|max:255',
                'settings.items.*.sub' => 'nullable|string|max:500',
                'settings.items.*.url' => 'nullable|string|max:255',
                'settings.items.*.existing_image' => 'nullable|string|max:255',
                'settings.items.*.image' => 'nullable|image|max:4096',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            $items = [];
            foreach (($settings['items'] ?? []) as $index => $item) {
                $image = $item['existing_image'] ?? null;
                if ($request->hasFile("settings.items.$index.image")) {
                    $image = $request->file("settings.items.$index.image")->store('homepage-slider', 'public');
                }
                if (! $image && ($item['heading'] ?? '') === '' && ($item['sub'] ?? '') === '') {
                    continue;
                }
                $items[] = [
                    'image' => $image,
                    'heading' => $item['heading'] ?? '',
                    'sub' => $item['sub'] ?? '',
                    'url' => $item['url'] ?? '',
                ];
            }

            return [
                'autoplay' => (bool) ($settings['autoplay'] ?? true),
                'items' => $items,
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if (in_array($blockType, ['service-section', 'product-group', 'post-group'], true)) {
            $settings = $request->validate([
                'settings.title_url' => 'nullable|string|max:255',
                'settings.subtitle' => 'nullable|string|max:1000',
                'settings.limit' => 'nullable|integer|min:1|max:24',
                'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
            ])['settings'] ?? [];

            return [
                'title_url' => $settings['title_url'] ?? '',
                'subtitle' => $settings['subtitle'] ?? '',
                'limit' => (int) ($settings['limit'] ?? 6),
                'display_mode' => $settings['display_mode'] ?? 'show',
            ];
        }

        if ($blockType === 'header') {
            $settings = $request->validate([
                'settings.brand_name' => 'nullable|string|max:120',
                'settings.brand_tag' => 'nullable|string|max:160',
                'settings.logo' => 'nullable|image|max:2048',
                'settings.show_hotline' => 'nullable|boolean',
                'settings.show_cta' => 'nullable|boolean',
                'settings.cta_text' => 'nullable|string|max:80',
                'settings.cta_url' => 'nullable|string|max:255',
            ])['settings'] ?? [];

            $logo = $existing['logo'] ?? null;
            if ($request->hasFile('settings.logo')) {
                $logo = $request->file('settings.logo')->store('homepage-header', 'public');
            }

            return [
                'brand_name' => $settings['brand_name'] ?? '',
                'brand_tag' => $settings['brand_tag'] ?? '',
                'logo' => $logo,
                'show_hotline' => (bool) ($settings['show_hotline'] ?? true),
                'show_cta' => (bool) ($settings['show_cta'] ?? true),
                'cta_text' => $settings['cta_text'] ?? '',
                'cta_url' => $settings['cta_url'] ?? '',
            ];
        }

        if ($blockType === 'topbar') {
            $settings = $request->validate([
                'settings.left_label' => 'nullable|string|max:120',
                'settings.bg_color' => 'nullable|string|max:20',
                'settings.show_auth' => 'nullable|boolean',
                'settings.items' => 'nullable|array',
                'settings.items.*.label' => 'nullable|string|max:120',
                'settings.items.*.url' => 'nullable|string|max:255',
            ])['settings'] ?? [];

            $items = [];
            foreach (($settings['items'] ?? []) as $item) {
                if (($item['label'] ?? '') === '') {
                    continue;
                }
                $items[] = ['label' => $item['label'], 'url' => $item['url'] ?? '#'];
            }

            return [
                'left_label' => $settings['left_label'] ?? '',
                'bg_color' => $settings['bg_color'] ?? '',
                'show_auth' => (bool) ($settings['show_auth'] ?? true),
                'items' => $items,
            ];
        }

        if (in_array($blockType, ['menu', 'social-links', 'footer'], true)) {
            $settings = $request->validate([
                'settings.copyright' => 'nullable|string|max:255',
                'settings.items' => 'nullable|array',
                'settings.items.*.label' => 'nullable|string|max:120',
                'settings.items.*.url' => 'nullable|string|max:255',
            ])['settings'] ?? [];

            $items = [];
            foreach (($settings['items'] ?? []) as $item) {
                if (($item['label'] ?? '') === '' && ($item['url'] ?? '') === '') {
                    continue;
                }
                $items[] = ['label' => $item['label'] ?? '', 'url' => $item['url'] ?? '#'];
            }

            $out = ['items' => $items];
            if ($blockType === 'footer') {
                $out['copyright'] = $settings['copyright'] ?? '';
            }

            return $out;
        }

        if ($blockType === 'editor') {
            $settings = $request->validate([
                'settings.content' => 'nullable|string|max:20000',
            ])['settings'] ?? [];

            return ['content' => $settings['content'] ?? ''];
        }

        if ($blockType === 'fanpage') {
            $settings = $request->validate([
                'settings.desc' => 'nullable|string|max:500',
                'settings.fb_url' => 'nullable|string|max:255',
                'settings.btn_text' => 'nullable|string|max:80',
                'settings.embed' => 'nullable|string|max:5000',
            ])['settings'] ?? [];

            return [
                'desc' => $settings['desc'] ?? '',
                'fb_url' => $settings['fb_url'] ?? '',
                'btn_text' => $settings['btn_text'] ?? '',
                'embed' => $settings['embed'] ?? '',
            ];
        }

        if ($blockType === 'product-filter') {
            $settings = $request->validate([
                'settings.limit' => 'nullable|integer|min:1|max:30',
                'settings.category_ids' => 'nullable|array',
                'settings.category_ids.*' => 'integer|exists:categories,id',
            ])['settings'] ?? [];

            return [
                'limit' => (int) ($settings['limit'] ?? 8),
                'category_ids' => array_values($settings['category_ids'] ?? []),
            ];
        }

        if ($blockType === 'about') {
            $settings = $request->validate([
                'settings.heading' => 'nullable|string|max:120',
                'settings.subheading' => 'nullable|string|max:255',
                'settings.description' => 'nullable|string|max:3000',
                'settings.image' => 'nullable|image|max:4096',
                'settings.cta_text' => 'nullable|string|max:80',
                'settings.cta_link' => 'nullable|string|max:255',
                'settings.stats' => 'nullable|array',
                'settings.stats.*.value' => 'nullable|string|max:30',
                'settings.stats.*.label' => 'nullable|string|max:120',
            ])['settings'] ?? [];

            $image = $existing['image'] ?? null;
            if ($request->hasFile('settings.image')) {
                $image = $request->file('settings.image')->store('homepage-about', 'public');
            }

            $stats = [];
            foreach (($settings['stats'] ?? []) as $item) {
                if (($item['value'] ?? '') === '' && ($item['label'] ?? '') === '') {
                    continue;
                }
                $stats[] = ['value' => $item['value'] ?? '', 'label' => $item['label'] ?? ''];
            }

            return [
                'heading' => $settings['heading'] ?? '',
                'subheading' => $settings['subheading'] ?? '',
                'description' => $settings['description'] ?? '',
                'image' => $image,
                'cta_text' => $settings['cta_text'] ?? '',
                'cta_link' => $settings['cta_link'] ?? '',
                'stats' => $stats,
            ];
        }

        if ($blockType !== 'banner') {
            return $existing;
        }

        $settings = $request->validate([
            'settings.image' => 'nullable|image|max:4096',
            'settings.width' => 'nullable|integer|min:0|max:5000',
            'settings.height' => 'nullable|integer|min:0|max:5000',
            'settings.radius' => 'nullable|integer|min:0|max:500',
            'settings.fullscreen' => 'nullable|boolean',
            'settings.content_source' => ['nullable', Rule::in(['manual', 'import'])],
            'settings.source_type' => ['nullable', Rule::in(['product', 'service', 'news'])],
            'settings.source_limit' => 'nullable|integer|min:1|max:6',
            'settings.display_mode' => ['nullable', Rule::in(['show', 'hide'])],
        ])['settings'] ?? [];

        $settings['image'] = $existing['image'] ?? null;

        if ($request->hasFile('settings.image')) {
            $settings['image'] = $request->file('settings.image')->store('homepage-banners', 'public');
        }

        return [
            'image' => $settings['image'],
            'width' => (int) ($settings['width'] ?? 0),
            'height' => (int) ($settings['height'] ?? 0),
            'radius' => (int) ($settings['radius'] ?? 0),
            'fullscreen' => (bool) ($settings['fullscreen'] ?? false),
            'content_source' => $settings['content_source'] ?? 'manual',
            'source_type' => $settings['source_type'] ?? 'product',
            'source_limit' => (int) ($settings['source_limit'] ?? 1),
            'display_mode' => $settings['display_mode'] ?? 'show',
        ];
    }

    public function widgetEdit(Request $request)
    {
        $user = AdminUser::find(session('admin_id'));
        abort_unless($user && $user->role === 'admin', 403);

        $type = $request->query('type', '');
        $variant = $request->query('variant', '');

        abort_unless(preg_match('/^[a-z0-9\-]+$/', $type) && preg_match('/^[a-zA-Z0-9_\-]+$/', $variant), 400);

        // Prefer the active theme's own widget copy; fall back to the standard set.
        $activeTheme = $this->themeManager->active();
        $themeCodeFile = base_path('themes/'.$activeTheme.'/widgets/'.$type.'/'.$variant.'.blade.php');
        $globalCodeFile = public_path('admin-assets/widgets/'.$type.'/'.$variant.'.blade.php');
        $codeFile = is_file($themeCodeFile) ? $themeCodeFile : $globalCodeFile;
        $metaFile = public_path('admin-assets/widgets/'.$type.'/meta.json');
        $imgDir = public_path('admin-assets/images/widgets/'.$type);

        $code = file_exists($codeFile) ? file_get_contents($codeFile) : '';
        $meta = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];

        $this->registry->load($activeTheme);
        $widgetDef = $this->registry->widgets()[$type] ?? [];
        $label = $meta['label'] ?? $widgetDef['label'] ?? $type;
        $allowedZones = $meta['allowed_zones'] ?? $widgetDef['allowed_zones'] ?? [];

        // Find image for this variant
        $imgUrl = null;
        foreach (['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'] as $ext) {
            $imgPath = $imgDir.'/'.$variant.'.'.$ext;
            if (file_exists($imgPath)) {
                $imgUrl = asset('admin-assets/images/widgets/'.$type.'/'.$variant.'.'.$ext);
                break;
            }
        }

        return response()->json([
            'type' => $type,
            'variant' => $variant,
            'label' => $label,
            'allowed_zones' => $allowedZones,
            'code' => $code,
            'img' => $imgUrl,
        ]);
    }

    public function widgetUpdate(Request $request)
    {
        $user = AdminUser::find(session('admin_id'));
        abort_unless($user && $user->role === 'admin', 403);

        $request->validate([
            'type' => 'required|regex:/^[a-z0-9\-]+$/',
            'variant' => 'required|regex:/^[a-zA-Z0-9_\-]+$/',
            'code' => 'nullable|string',
            'widget_label' => 'nullable|string|max:100',
            'widget_zones' => 'nullable|array',
            'widget_image' => 'nullable|image|max:2048',
        ]);

        $type = $request->input('type');
        $variant = $request->input('variant');

        // Write the edited blade into the ACTIVE THEME's own widget folder so the
        // shared standard set (public/admin-assets/widgets) is never mutated. Each theme
        // keeps an isolated copy; customizing one theme never affects others.
        $activeTheme = $this->themeManager->active();
        $codeDir = base_path('themes/'.$activeTheme.'/widgets/'.$type);
        if (! is_dir($codeDir)) {
            mkdir($codeDir, 0755, true);
        }
        file_put_contents($codeDir.'/'.$variant.'.blade.php', $request->input('code', ''));

        // Image + meta remain shared metadata (preview/label/zones), kept in the standard area.
        $metaDir = public_path('admin-assets/widgets/'.$type);
        if (! is_dir($metaDir)) {
            mkdir($metaDir, 0755, true);
        }

        // Save image if uploaded
        if ($request->hasFile('widget_image')) {
            $imgDir = public_path('admin-assets/images/widgets/'.$type);
            if (! is_dir($imgDir)) {
                mkdir($imgDir, 0755, true);
            }
            $file = $request->file('widget_image');
            $ext = $file->getClientOriginalExtension();
            $file->move($imgDir, $variant.'.'.$ext);
        }

        // Save meta.json with label + allowed_zones
        $metaFile = $metaDir.'/meta.json';
        $meta = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];
        if ($request->filled('widget_label')) {
            $meta['label'] = $request->input('widget_label');
        }
        if ($request->has('widget_zones')) {
            $meta['allowed_zones'] = $request->input('widget_zones', []);
        }
        $meta['key'] = $type;
        file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['ok' => true, 'message' => 'Đã lưu widget.']);
    }

    public function widgetStore(Request $request)
    {
        $request->validate([
            'widget_key' => 'required|regex:/^[a-z0-9\-]+$/',
            'widget_label' => 'required|max:100',
            'widget_zones' => 'required|array|min:1',
            'widget_images' => 'nullable|array',
            'widget_images.*' => 'image|max:2048',
            'widget_code' => 'nullable|string',
        ]);

        $key = $request->input('widget_key');
        $label = $request->input('widget_label');
        $zones = $request->input('widget_zones', []);
        $code = $request->input('widget_code', '');

        // Save images
        $imgDir = public_path('admin-assets/images/widgets/'.$key);
        if (! is_dir($imgDir)) {
            mkdir($imgDir, 0755, true);
        }
        if ($request->hasFile('widget_images')) {
            foreach ($request->file('widget_images') as $file) {
                $file->move($imgDir, $file->getClientOriginalName());
            }
        }

        // Save widget code
        $codeDir = public_path('admin-assets/widgets/'.$key);
        if (! is_dir($codeDir)) {
            mkdir($codeDir, 0755, true);
        }
        if ($code) {
            file_put_contents($codeDir.'/widget.blade.php', $code);
        }

        // Save widget meta (json)
        $meta = [
            'key' => $key,
            'label' => $label,
            'allowed_zones' => $zones,
        ];
        file_put_contents($codeDir.'/meta.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.homepage-layout.index')
            ->with('success', "Đã thêm widget '{$label}'. Để dùng trong theme, thêm vào widgets.php của theme tương ứng.");
    }

    private function scanWidgetVariants(array $blockTypes): array
    {
        // Returns: [ 'header' => [ ['key'=>'menu_classic','label'=>'Menu Classic','img'=>'...'], ... ], ... ]
        $variants = [];
        $imgBase = public_path('admin-assets/images/widgets');
        $metaBase = public_path('admin-assets/widgets');

        foreach (array_keys($blockTypes) as $type) {
            $imgDir = $imgBase.DIRECTORY_SEPARATOR.$type;
            $metaDir = $metaBase.DIRECTORY_SEPARATOR.$type;
            $files = is_dir($imgDir) ? (glob($imgDir.'/*.{png,jpg,jpeg,gif,webp,svg}', GLOB_BRACE) ?: []) : [];

            if (empty($files)) {
                // No images — one generic card with no image
                $variants[$type] = [
                    ['key' => $type, 'label' => null, 'img' => null],
                ];

                continue;
            }

            $variants[$type] = [];
            foreach ($files as $file) {
                $slug = pathinfo($file, PATHINFO_FILENAME);
                $label = ucwords(str_replace(['-', '_'], ' ', $slug));
                $url = asset('admin-assets/images/widgets/'.$type.'/'.basename($file));
                $variants[$type][] = ['key' => $slug, 'label' => $label, 'img' => $url];
            }
        }

        return $variants;
    }

    private function ensureThemeDefaults(string $theme): void
    {
        if (HomepageBlock::where('theme_name', $theme)->exists()) {
            return;
        }

        foreach ($this->registry->defaults() as $index => $block) {
            HomepageBlock::create($block + [
                'theme_name' => $theme,
                'sort_order' => $index + 1,
                'settings' => [],
                'is_active' => true,
            ]);
        }
    }
}
