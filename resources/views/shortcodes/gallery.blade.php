<div class="cms-shortcode-gallery">
  @foreach($images as $image)
    <a href="{{ $image }}" target="_blank" rel="noopener">
      <img src="{{ $image }}" alt="" loading="lazy">
    </a>
  @endforeach
</div>
