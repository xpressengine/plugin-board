{{ XeFrontend::js('plugins/board/assets/js/board.js')->appendTo('body')->load() }}
{{ XeFrontend::js('assets/core/xe-ui-component/js/xe-page.js')->appendTo('body')->load() }}

{{ XeFrontend::css('plugins/board/assets/css/new-board-common.css')->load() }}
{{ XeFrontend::css('plugins/board/assets/css/new-board-header.css')->load() }}
{{ XeFrontend::css('plugins/board/assets/css/new-board-footer.css')->load() }}

{{ expose_trans('board::selectPost') }}
{{ expose_trans('board::selectBoard') }}
{{ expose_trans('board::msgDeleteConfirm') }}

        
<section class="xf-board">
    @if ($config->get('topCommonContentOnlyList') === false || request()->segment(2) === '')
        <div class="xe-list-board-header__text">
            {!! xe_trans($config->get('topCommonContent', '')) !!}
        </div>
    @endif
    <section class="xe-list-board">
        <div class="xe-list-board-header">
            @if (request()->segment(2) === null)
                <div class="xe-list-board-header__title-content">
                    <div class="xe-list-board-header__title-box">
                        @if (in_array(array_get($skinConfig, 'titleStyle', 'titleWithCount'), ['titleWithCount', 'title']) === true)
                            <h2 class="xe-list-board-header__title">
                                <a href="{{ $urlHandler->get('index') }}">
                                    @if (xe_trans($config->get('boardName', '')) !== '')
                                        {{ xe_trans($config->get('boardName')) }}
                                    @else
                                        {{ xe_trans(current_menu()['title']) }}
                                    @endif
                                </a>
                            </h2>
                            @if (array_get($skinConfig, 'titleStyle', 'titleWithCount') === 'titleWithCount')
                                <span class="xe-list-board-header__post-count">({{ number_format($paginate->total()) }})</span>
                            @endif
                        @endif
                    </div>
                    @if (array_get($skinConfig, 'visibleIndexMobileWriteButton', 'on') === 'on')
                        @if (array_get($skinConfig, 'visibleIndexWriteButton', 'show') === 'show')
                            <div class="xe-list-board-header__write-button">
                                <a href="{{ $urlHandler->get('create') }}"><img src="{{ url('plugins/board/assets/img/pencil.svg') }}" alt="모바일 글쓰기 이미지"></a>
                            </div>
                        @elseif (request()->segment(2) === null && array_get($skinConfig, 'visibleIndexWriteButton', 'show') === 'permission' && $isWritable === true)
                            <div class="xe-list-board-header__write-button">
                                <a href="{{ $urlHandler->get('create') }}"><img src="{{ url('plugins/board/assets/img/pencil.svg') }}" alt="모바일 글쓰기 이미지"></a>
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        </div>
        @section('content')
            {!! isset($content) ? $content : '' !!}
        @show
    </section>
    @if ($config->get('bottomCommonContentOnlyList') === false || request()->segment(2) === '')
        <div class="xe-list-board-footer__text">
            {!! xe_trans($config->get('bottomCommonContent', '')) !!}
        </div>
    @endif
</section>
