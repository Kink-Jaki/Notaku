@if ($paginator->hasPages())
    <ul class="pagination mb-0">
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            @if ($paginator->onFirstPage())
                <span class="page-link" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" focusable="false">
                        <path d="M11.854 3.646a.5.5 0 0 1 0 .708L6.207 8l5.647 4.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </span>
            @else
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">
                    <svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" focusable="false" aria-hidden="true">
                        <path d="M11.854 3.646a.5.5 0 0 1 0 .708L6.207 8l5.647 4.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </a>
            @endif
        </li>

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            @if ($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">
                    <svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" focusable="false" aria-hidden="true">
                        <path d="M4.646 3.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </a>
            @else
                <span class="page-link" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" focusable="false">
                        <path d="M4.646 3.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </span>
            @endif
        </li>
    </ul>
@endif
