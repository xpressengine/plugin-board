<a href="#"
   class="bd_ico xe-share"
   data-toggle="xe-page-toggle-menu"
   data-url="{{route('toggleMenuPage')}}"
   data-data='{!! json_encode(['id'=>$item->id, 'type'=>'uiobject/board@share', 'instanceId'=>$item->instanceId, 'url'=>$url]) !!}'
   data-side="dropdown-menu-right">
    <i class="xi-external-link"></i><span class="xe-sr-only">{{ xe_trans('board::share') }}</span>
</a>
