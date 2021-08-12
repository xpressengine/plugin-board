{{ XeFrontend::js('plugins/board/assets/js/managerSkin.js')->load() }}

@section('page_title')
    <h2>
        글로벌 설정 - {{ xe_trans($_menu[$_active]->getTitle()) }}
    </h2>
@stop

<ul class="nav nav-tabs">
    @foreach($_menu as $key => $menu)
        <li @if($_active === $key) class="active" @endif>
            <a href="{{ $menu->getLinkFunction()() }}" @if ($menu->getIsExternalLink()) target="_blank" @endif>
                @if ($menu->getIcon())
                    <i class="{{ $menu->getIcon() }}"></i>
                @endif

                {{ xe_trans($menu->getTitle()) }}
            </a>
        </li>
    @endforeach
</ul>

{!! $content !!}
