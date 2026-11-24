@if ($paginator->hasPages())
<div class="pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span style="opacity:.4;cursor:default;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;background:#fff">‹</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}">‹</a>
    @endif

    {{-- Pages --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;background:#fff">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="current">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}">›</a>
    @else
        <span style="opacity:.4;cursor:default;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;background:#fff">›</span>
    @endif
</div>
@endif
