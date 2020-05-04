@php
    use Xpressengine\Plugins\Board\Components\UIObjects\NewSelect\NewSelectUIObject;
@endphp

@if(isset($items) && is_array($items) && count($items))
    @foreach ($items as $item)
        <li class="xe-list-board-header--dropdown-menu-item @if ($selectedItemValue === (string)$item['value']) on @endif" data-value="{{ $item['value'] }}">
            <a href="#">{{ xe_trans($item['text']) }}</a>
        </li>
        <ul class="xe-list-board-header--dropdown-sub-menu">
            @if (NewSelectUIObject::hasChildren($item))
                {!! NewSelectUIObject::renderList(NewSelectUIObject::getChildren($item), $selectedItemValue) !!}
            @endif
        </ul>
    @endforeach
@endif
