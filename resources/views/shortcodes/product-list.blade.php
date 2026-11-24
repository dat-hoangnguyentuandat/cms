<div class="cms-shortcode-product-list">
  @if($title)
    <h3>{{ $title }}</h3>
  @endif
  <div class="products-grid">
    @foreach($products as $product)
      <article class="product-card">
        @if($product->image_url)
          <div class="product-img-wrap">
            <a href="{{ route('product.show', $product->slug) }}">
              <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
            </a>
          </div>
        @endif
        <div class="product-body">
          @if($product->category)
            <div class="product-brand">{{ $product->category->name }}</div>
          @endif
          <h3 class="product-name"><a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a></h3>
          @if($product->short_desc)
            <div class="product-summary rich-content">{!! cms_content($product->short_desc, $product) !!}</div>
          @endif
          <div class="product-price-row">
            <span class="price-now">{{ number_format($product->display_price, 0, ',', '.').'đ' }}</span>
          </div>
        </div>
      </article>
    @endforeach
  </div>
</div>
