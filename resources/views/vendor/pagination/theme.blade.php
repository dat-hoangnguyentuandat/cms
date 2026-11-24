{{--
  Theme-agnostic pagination view for CMS themes.
  Uses plain .pagination / .page-item / .page-link classes (NOT Tailwind utilities)
  and SVG arrows with explicit width/height ATTRIBUTES so they render correctly
  even though themes do not load Tailwind. Style via each theme's `.pagination` CSS.
  Usage in a theme listing view:  {{ $items->links('pagination::theme') }}
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination">
        <ul class="pagination">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    </a>
                </li>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
