<div class="bd_share_area">
    <a href="#" class="bd_ico bd_share"><i class="xi-external-link"></i><span class="xe-sr-only">{{ trans('board::share') }}</span></a>
    <div class="ly_popup">
        <ul>
            @foreach ($items as $item)
                <li><a href="{{$item['url']}}" target="_blank">
                        @if($item['icon'] != '')<i class="{{$item['icon']}}"></i>@endif
                        {{ xe_trans($item['label']) }}
                    </a></li>
            @endforeach
        </ul>
    </div>
</div>
