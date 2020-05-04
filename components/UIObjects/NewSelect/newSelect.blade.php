@php
    use Xpressengine\Plugins\Board\Components\UIObjects\NewSelect\NewSelectUIObject;
@endphp

<div class="xe-list-board-header--dropdown __xe-dropdown-form">
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-valid-name="{{ xe_trans($label) }}">
    
    <div class="xe-list-board-header-{{ $name }}__button xe-list-board-header--dropdown__button">
        <div class="xe-list-board--header-dropdown__button-box">
            <button class="xe-list-board--header-dropdown__button-text" type="button"
                    @if ($open_target !== '') data-target="{{ $open_target }}" @endif
                    data-toggle="xe-dropdown" aria-expanded="false">{{ (string)$value !== (string)$default ? xe_trans($text) : xe_trans($label) }} <i class="xi-angle-down-thin"></i></button>
        </div>
        
        <ul class="xe-list-board-header--dropdown-menu" data-name="{{ $name }}">
            <li class="xe-list-board-header--dropdown-menu-item @if ((string)$value === (string)$default) on @endif">
                <a href="#">{{ xe_trans($label) }}</a>
            </li>
            {!! NewSelectUIObject::renderList($items, $value) !!}
        </ul>
    </div>
</div>
