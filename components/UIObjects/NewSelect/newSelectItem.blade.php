@php
    use Xpressengine\Plugins\Board\Components\UIObjects\NewSelect\NewSelectUIObject;
@endphp

@if(isset($items) && is_array($items) && count($items))
    @foreach ($items as $item)
        <li class="xe-list-board-header--dropdown-menu-item @if ($selectedItemValue === (string)$item['value']) on @endif">
            <a href="#" data-value="{{ $item['value'] }}">{{ xe_trans($item['text']) }}</a>
            @if (NewSelectUIObject::hasChildren($item))
                <ul class="xe-list-board-header--dropdown-sub-menu">
                    {!! NewSelectUIObject::renderList(NewSelectUIObject::getChildren($item), $selectedItemValue) !!}
                </ul>
            @endif
        </li>
    @endforeach
@endif
