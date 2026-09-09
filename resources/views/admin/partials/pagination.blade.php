@if ($paginator->hasPages())
    <nav class="ma-pagination" aria-label="Pagination">
        <div class="ma-pagination__summary">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }} · {{ $paginator->total() }} records
        </div>
        <div class="ma-pagination__actions">
            @if ($paginator->onFirstPage())
                <span class="ma-button ma-button--outline ma-button--compact ma-button--disabled" aria-disabled="true">Previous</span>
            @else
                <a class="ma-button ma-button--outline ma-button--compact" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="ma-button ma-button--outline ma-button--compact" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="ma-button ma-button--outline ma-button--compact ma-button--disabled" aria-disabled="true">Next</span>
            @endif
        </div>
    </nav>
@endif
