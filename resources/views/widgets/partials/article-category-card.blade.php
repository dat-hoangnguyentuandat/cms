<a class="article-card" href="{{ route('news.show', $post->slug) }}">
  <span class="article-card-image">
    <img src="{{ $post->image_url ?: asset('theme-assets/satc/images/banner.jpg') }}" alt="{{ $post->title }}" loading="lazy">
  </span>
  <span class="article-card-title">{{ $post->title }}</span>
  @if($post->category)
    <span class="article-card-meta">{{ $post->category->name }}</span>
  @endif
</a>
