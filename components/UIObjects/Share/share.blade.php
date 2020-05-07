<a href="#"
   title="공유하기"
   class="bd_ico xe-share @if(isset($className)) {{ $className }} @endif"
   data-toggle="xe-page-toggle-menu"
   data-url="{{route('toggleMenuPage')}}"
   data-data='{!! json_encode(['id'=>$item->id, 'type'=>'uiobject/board@share', 'instanceId'=>$item->instance_id, 'url'=>$url]) !!}'
   data-side="dropdown-menu-right">
    <i class="xi-share-alt-o"></i><span class="xe-sr-only">{{ xe_trans('board::share') }}</span>
</a>

<style>
    .xe-share {
        color: #141414;
        text-decoration: none;
    }
    .xe-share:hover,
    .xe-share:active,
    .xe-share:visited {
        color: #141414;
        text-decoration: none;
    }
</style>
