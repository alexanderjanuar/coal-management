@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="cu-pager">
        <div class="cu-pager-info">
            Showing
            <strong>{{ $paginator->firstItem() ?? 0 }}</strong>
            –
            <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
            of
            <strong>{{ $paginator->total() }}</strong>
        </div>

        <div class="cu-pager-controls">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="cu-pager-btn cu-pager-btn-icon disabled" aria-disabled="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            @else
                <button type="button" wire:click="previousPage" wire:loading.attr="disabled" class="cu-pager-btn cu-pager-btn-icon" aria-label="Previous">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="cu-pager-ellipsis">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="cu-pager-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }})" class="cu-pager-btn">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" wire:loading.attr="disabled" class="cu-pager-btn cu-pager-btn-icon" aria-label="Next">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            @else
                <span class="cu-pager-btn cu-pager-btn-icon disabled" aria-disabled="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
