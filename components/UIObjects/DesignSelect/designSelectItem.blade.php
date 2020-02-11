@php
    use Xpressengine\Plugins\Board\Components\UIObjects\DesignSelect\DesignSelectUIObject;
@endphp

<ul class="xe-dropdown-menu__sub">
    @foreach ($items as $item)
        <li @if($selectedItemValue == (string)$item['value']) class="on" @endif>
            <a href="#" data-value="{{$item['value']}}">
                {{ xe_trans($item['text']) }}
            </a>
            @if (DesignSelectUIObject::hasChildren($item))
                {!! DesignSelectUIObject::renderList(DesignSelectUIObject::getChildren($item), $selectedItemValue) !!}
            @endif
        </li>
    @endforeach
</ul>
