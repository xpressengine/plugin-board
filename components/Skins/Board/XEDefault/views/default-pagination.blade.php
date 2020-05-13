{{ XeFrontend::css('plugins/board/assets/css/new-board-pagination.css')->load() }}

<div class="xe-list-board--pagination xe-list-board--pagination-pc">
    <ul class="xe-list-board--pagination-list">
        <li class="xe-list-board__pagination-item xe-list-board__btn_pagination xe-list-board__btn_prev">
            @if ($paginator->currentPage() <= 1)
                <a class="xe-list-board__pagination-item-link xe-list-board__pagination-item-disabled-link" onclick="return false;"><i class="xi-angle-left"></i></a>
            @else
                <a class="xe-list-board__pagination-item-link" href="{{ $paginator->previousPageUrl() }}"><i class="xi-angle-left"></i></a>
            @endif
        </li>
        
        @foreach ($elements as $element)
            @if (is_array($element) === true)
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <li class="xe-list-board__pagination-item xe-list-board__pagination-number xe-list-board__pagination-number--active">
                            <a class="xe-list-board__pagination-item-link" onclick="return false;">{{ $page }}</a>
                        </li>
                    @else
                        <li class="xe-list-board__pagination-item xe-list-board__pagination-number">
                            <a class="xe-list-board__pagination-item-link" href="{{ htmlentities($url) }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @elseif (is_string($element) === true)
                <li class="xe-list-board__pagination-item xe-list-board__pagination-number">
                    <a class="xe-list-board__pagination-item-link" onclick="return false;">{{ $element }}</a>
                </li>
            @endif
        @endforeach
        
        <li class="xe-list-board__pagination-item xe-list-board__btn_pagination xe-list-board__btn_next">
            @if ($paginator->hasMorePages() === true)
                <a class="xe-list-board__pagination-item-link" href="{{ $paginator->nextPageUrl() }}"><i class="xi-angle-right"></i></a>
            @else
                <a class="xe-list-board__pagination-item-link xe-list-board__pagination-item-disabled-link" onclick="return false;"><i class="xi-angle-right"></i></a>
            @endif
        </li>
    </ul>
</div>

<div class="xe-list-board--pagination xe-list-board--pagination-mobile">
    <ul class="xe-list-board--pagination-list">
        <li class="xe-list-board__pagination-item xe-list-board__btn_pagination xe-list-board__btn_prev">
            @if ($paginator->currentPage() <= 1)
                <a class="xe-list-board__pagination-item-link xe-list-board__pagination-item-disabled-link" onclick="return false;"><i class="xi-angle-left"></i></a>
            @else
                <a class="xe-list-board__pagination-item-link" href="{{ $paginator->previousPageUrl() }}"><i class="xi-angle-left"></i></a>
            @endif
        </li>
        <li class="xe-list-board__pagination-item xe-list-board__pagination-box">
            <span class="xe-list-board__pagination-number-present">{{ $paginator->currentPage() }}</span> / <span class="xe-list-board__pagination-number-total">{{ $paginator->lastPage() }}</span>
        </li>
        <li class="xe-list-board__pagination-item xe-list-board__btn_pagination xe-list-board__btn_next">
            @if ($paginator->hasMorePages() === true)
                <a class="xe-list-board__pagination-item-link" href="{{ $paginator->nextPageUrl() }}"><i class="xi-angle-right"></i></a>
            @else
                <a class="xe-list-board__pagination-item-link xe-list-board__pagination-item-disabled-link" onclick="return false;"><i class="xi-angle-right"></i></a>
            @endif
        </li>
    </ul>
</div>
