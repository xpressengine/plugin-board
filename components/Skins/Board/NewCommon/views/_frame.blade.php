{{ XeFrontend::js('plugins/board/assets/js/board.js')->appendTo('body')->load() }}
{{ XeFrontend::js('assets/core/xe-ui-component/js/xe-page.js')->appendTo('body')->load() }}

{{ XeFrontend::css('plugins/board/assets/css/new-board-header.css')->load() }}
{{ XeFrontend::css('plugins/board/assets/css/new-board-footer.css')->load() }}
{{ XeFrontend::css('plugins/board/assets/css/new-board-common.css')->load() }}

<section class="xe-list-board container">
    <div class="xe-list-board-header">
        <div class="xe-list-board-header__title-content">
{{--            TODO 출력 옵션 적용--}}
            <div class="xe-list-board-header__title-box">
                <h2 class="xe-list-board-header__title"><a href="{{ $urlHandler->get('index') }}">{{ xe_trans(current_menu()['title']) }}</a></h2>
                @if (request()->segment(2) === null)
                    <span class="xe-list-board-header__post-count">({{ number_format($paginate->total()) }})</span>
                @endif
            </div>
            <div class="xe-list-board-header__write-button">
                <a href="{{ $urlHandler->get('create') }}"><img src="../plugins/board/assets/img/pencil.svg"></a>
            </div>
        </div>
        
        @if ($config->get('topCommonContentOnlyList') === false || request()->segment(2) === '')
            <div class="xe-list-board-header__text">
                {!! xe_trans($config->get('topCommonContent', '')) !!}
            </div>
        @endif
    </div>

    @section('content')
        {!! isset($content) ? $content : '' !!}
    @show
    
    @if ($config->get('bottomCommonContentOnlyList') === false || request()->segment(2) === '')
        <div class="xe-list-board-footer__text">
            {!! xe_trans($config->get('bottomCommonContent', '')) !!}
        </div>
    @endif
</section>
