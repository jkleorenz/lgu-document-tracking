@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $maxPages = 7;
                
                // Generate custom pagination elements
                $customElements = [];
                
                if ($lastPage <= $maxPages) {
                    // Show all pages if total pages is less than or equal to max
                    for ($i = 1; $i <= $lastPage; $i++) {
                        $customElements[$i] = $paginator->url($i);
                    }
                } else {
                    // Show limited pages with ellipsis
                    if ($currentPage <= 4) {
                        // Current page is in the first 4 pages
                        for ($i = 1; $i <= 5; $i++) {
                            $customElements[$i] = $paginator->url($i);
                        }
                        $customElements['...'] = 'ellipsis';
                        $customElements[$lastPage] = $paginator->url($lastPage);
                    } elseif ($currentPage >= $lastPage - 3) {
                        // Current page is in the last 4 pages
                        $customElements[1] = $paginator->url(1);
                        $customElements['...'] = 'ellipsis';
                        for ($i = $lastPage - 4; $i <= $lastPage; $i++) {
                            $customElements[$i] = $paginator->url($i);
                        }
                    } else {
                        // Current page is in the middle
                        $customElements[1] = $paginator->url(1);
                        $customElements['...'] = 'ellipsis';
                        for ($i = $currentPage - 1; $i <= $currentPage + 1; $i++) {
                            $customElements[$i] = $paginator->url($i);
                        }
                        $customElements['...'] = 'ellipsis';
                        $customElements[$lastPage] = $paginator->url($lastPage);
                    }
                }
            @endphp
            
            @foreach ($customElements as $page => $url)
                @if ($page === '...')
                    <li class="disabled" aria-disabled="true"><span>...</span></li>
                @elseif ($page == $currentPage)
                    <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                @else
                    <li><a href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @else
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
