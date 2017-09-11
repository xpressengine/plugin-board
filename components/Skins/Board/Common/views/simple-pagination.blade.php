@if ($paginator->hasPages())
    <div class="bd_paginate v2 xe-visible-xs">
        @if($paginator->currentPage() <= 1)
            <span class="btn_pg btn_prev"><i class="xi-angle-left"><span class="bd_hidden">&laquo;</span></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn_pg btn_prev"><i class="xi-angle-left"><span class="bd_hidden">&laquo;</span></i></a>
        @endif

            <span class="pg_box"><strong>{{ $paginator->currentPage() }}</strong> / <span>{{ $paginator->lastPage() }}</span></span>


        @if(!$paginator->hasMorePages())
            <span class="btn_pg btn_next"><i class="xi-angle-right"><span class="bd_hidden">&raquo;</span></i></span>
        @else
            <a href="{{ $paginator->nextPageUrl() }}" class="btn_pg btn_next"><i class="xi-angle-right"><span class="bd_hidden">&raquo;</span></i></a>
        @endif
    </div>
@endif

