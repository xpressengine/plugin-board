@php
    use Xpressengine\Plugins\Board\Components\UIObjects\NewSelect\NewSelectUIObject;
@endphp

<div class="new-select __xe-dropdown-form">
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-valid-name="{{ xe_trans($label) }}">
    <div class="xe-list-board-header-{{ $name }}__button xe-dropdown__button">
        <div class="xe-dropdown__button-box">
            <button class="xe-dropdown__button-text" type="button"
                    @if ($open_target !== '') data-target="{{ $open_target }}" @endif
                    data-toggle="xe-dropdown" aria-expanded="false">
                    {{ (string)$value !== (string)$default ? xe_trans($text) : xe_trans($label) }}
                </button>
        </div>
        <div class="xe-dropdown--menu xe-dropdown--menu--{{ $name }}" data-name="{{ $name }}">
                <div class="xe-dropdown--menu-item @if ((string)$value === (string)$default) on @endif">
            <a href="#" class="xe-dropdown--menu-item-link">
                    {{ xe_trans($label) }}
            </a>
                </div>
            {!! NewSelectUIObject::renderList($items, $value) !!}
        </div>
    </div>
</div>
