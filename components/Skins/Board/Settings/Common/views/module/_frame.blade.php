{{ XeFrontend::js('plugins/board/assets/js/managerSkin.js')->load() }}
{{ XeFrontend::js('assets/vendor/jqueryui/jquery-ui.min.js')->load() }}

@section('page_title')
    <h2>
        {{ $config->get('board_name') }} - {{ xe_trans($_activeMenu->getTitle()) }}
    </h2>
@stop

{{-- $_active 는 SettingsSkin 에서 처리됨 --}}
<ul class="nav nav-tabs">
    @foreach($_menus as $key => $menu)
        @continue($menu->getLinkFunction()($boardId) === null)

        <li @if($_active === $key) class="active" @endif>
            <a href="{{ $menu->getLinkFunction()($boardId) }}" @if ($menu->getIsExternalLink()) target="_blank" @endif>
                @if ($menu->getIcon())
                    <i class="{{ $menu->getIcon() }}"></i>
                @endif

                {{ xe_trans($menu->getTitle()) }}
            </a>
        </li>
    @endforeach
</ul>

{!! $content !!}

