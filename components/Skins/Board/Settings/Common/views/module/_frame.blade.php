{{ XeFrontend::js('plugins/board/assets/js/managerSkin.js')->load() }}
{{ XeFrontend::js('assets/vendor/jqueryui/jquery-ui.min.js')->load() }}

{{-- $_active 는 SettingsSkin 에서 처리됨 --}}
<ul class="nav nav-tabs">
    @foreach($_menu as $key => $menuItem)
        <li @if($_active === $key) class="active" @endif>
            <a href="{{ $menuItem['link_func']($boardId) }}">{{ $menuItem['title'] }}</a>
        </li>
    @endforeach
</ul>

{!! $content !!}

