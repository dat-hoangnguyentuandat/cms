<a class="product-card" href="{{ route('product.show', $product->slug) }}">
  <span class="product-card-image">
    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
  </span>
  <span class="product-card-title">{{ $product->name }}</span>
  <span class="product-card-price">
    {{ number_format((float) $product->display_price, 0, ',', '.') }}đ
    @if($product->sale_price && $product->price && $product->sale_price < $product->price)
      <span class="product-card-old-price">{{ number_format((float) $product->price, 0, ',', '.') }}đ</span>
    @endif
  </span>
</a>
