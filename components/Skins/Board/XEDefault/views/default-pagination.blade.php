<!-- PC 페이지네이션 -->
<div class="xf-board-pagination-pc xf-pc-display-bl">
    <ul class="xf-pagination-list">
        <li class="xf-pagination-item xf-pagination-arrow">
            @if ($paginator->currentPage() <= 1)
                <a class="xf-pagination__link xf-pagination__disabled-link" onclick="return false;"><i class="xi-angle-left"></i></a>
            @else
                <a class="xf-pagination__link" href="{{ $paginator->previousPageUrl() }}"><i class="xi-angle-left"></i></a>
            @endif
        </li>

        @foreach ($elements as $element)
            @if (is_array($element) === true)
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <li class="xf-pagination-item xf-pagination-number xf-pagination-number__active">
                            <a class="xf-pagination__link" onclick="return false;">{{ $page }}</a>
                        </li>
                    @else
                        <li class="xf-pagination-item xf-pagination-number">
                            <a class="xf-pagination__link" href="{{ htmlentities($url) }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @elseif (is_string($element) === true)
                <li class="xf-pagination-item xf-pagination-number">
                    <a class="xf-pagination__link" onclick="return false;">{{ $element }}</a>
                </li>
            @endif
        @endforeach

        <li class="xf-pagination-item xf-pagination-arrow">
            @if ($paginator->hasMorePages() === true)
                <a class="xf-pagination__link" href="{{ $paginator->nextPageUrl() }}"><i class="xi-angle-right"></i></a>
            @else
                <a class="xf-pagination__link xf-pagination__disabled-link" onclick="return false;"><i class="xi-angle-right"></i></a>
            @endif
        </li>
    </ul>
</div>
<!-- //PC 페이지네이션 -->

<!-- MOBILE 페이지네이션 -->
<div class="xf-board-pagenation-mobile xf-mo-display-bl">
    <ul class="xf-pagination-list">
        <li class="xf-pagination-item xf-pagination-arrow">
            @if ($paginator->currentPage() <= 1)
                <a class="xf-pagination__link xf-pagination__disabled-link" onclick="return false;"><i class="xi-angle-left"></i></a>
            @else
                <a class="xf-pagination__link" href="{{ $paginator->previousPageUrl() }}"><i class="xi-angle-left"></i></a>
            @endif
        </li>
        <li class="xf-pagination-item xe-list-board__pagination-box">
            <span class="xf-pagination-number">{{ $paginator->currentPage() }}</span>
            <span class="xf-pagination-number">/</span>
            <span class="xf-pagination-number">{{ $paginator->lastPage() }}</span>
        </li>
        <li class="xf-pagination-item xf-pagination-arrow">
            @if ($paginator->hasMorePages() === true)
                <a class="xf-pagination__link" href="{{ $paginator->nextPageUrl() }}"><i class="xi-angle-right"></i></a>
            @else
                <a class="xf-pagination__link xf-pagination__disabled-link" onclick="return false;"><i class="xi-angle-right"></i></a>
            @endif
        </li>
    </ul>
</div>
<!-- //MOBILE 페이지네이션 -->

