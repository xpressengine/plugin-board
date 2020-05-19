@php
    use Xpressengine\Plugins\Board\Components\UIObjects\NewSelect\NewSelectUIObject;
@endphp

@if(isset($items) && is_array($items) && count($items))
    @foreach ($items as $item)
        <div class="xe-dropdown--menu-item @if ((string)$selectedItemValue === (string)$item['value']) on @endif" data-value="{{ $item['value'] }}">
            <a href="#" class="xe-dropdown--menu-item-link">{{ xe_trans($item['text']) }}</a>
        </div>
        <div class="xe-dropdown--sub-menu">
            @if (NewSelectUIObject::hasChildren($item))
                {!! NewSelectUIObject::renderList(NewSelectUIObject::getChildren($item), $selectedItemValue) !!}
            @endif
        </div>
    @endforeach
@endif