@if ($paginator->hasPages())
<div class="bd_paginate xe-hidden-xs">
    @if($paginator->currentPage() <= 1)
        <span class="btn_pg btn_prev"><i class="xi-angle-left"><span class="bd_hidden">&laquo;</span></i></span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="btn_pg btn_prev"><i class="xi-angle-left"><span class="bd_hidden">&laquo;</span></i></a>
    @endif

        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span>{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <strong>{{ $page }}</strong>
                    @else
                        <a href="{{ htmlentities($url) }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach


    @if(!$paginator->hasMorePages())
        <span class="btn_pg btn_next"><i class="xi-angle-right"><span class="bd_hidden">&raquo;</span></i></span>
    @else
        <a href="{{ $paginator->nextPageUrl() }}" class="btn_pg btn_next"><i class="xi-angle-right"><span class="bd_hidden">&raquo;</span></i></a>
    @endif
</div>
@endif

