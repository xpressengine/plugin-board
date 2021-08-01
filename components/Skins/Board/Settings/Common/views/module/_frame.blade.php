{{ XeFrontend::js('plugins/board/assets/js/managerSkin.js')->load() }}
{{ XeFrontend::js('assets/vendor/jqueryui/jquery-ui.min.js')->load() }}

@section('page_title')
    <h2>
        {{ $config->get('board_name') }} - {{ $_menu[$_active]['title'] }}
    </h2>
@stop

{{-- $_active 는 SettingsSkin 에서 처리됨 --}}
<ul class="nav nav-tabs">
    @foreach($_menu as $key => $menuItem)
        @continue($menuItem['link_func']($boardId) === null)

        <li @if($_active === $key) class="active" @endif>
            <a href="{{ $menuItem['link_func']($boardId) }}" @if (\Illuminate\Support\Arr::get($menuItem, 'external_link', false)) target="_blank" @endif>
                {{ $menuItem['title'] }}

                @if (\Illuminate\Support\Arr::get($menuItem, 'external_link', false))
                    <i class="xi-external-link"></i>
                @endif
            </a>
        </li>
    @endforeach
</ul>

{!! $content !!}

