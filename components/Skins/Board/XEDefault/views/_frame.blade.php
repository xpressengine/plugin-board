{{ XeFrontend::js('plugins/board/assets/js/board.js')->appendTo('body')->load() }}
{{ XeFrontend::js('assets/core/xe-ui-component/js/xe-page.js')->appendTo('body')->load() }}

{{ XeFrontend::css('plugins/board/assets/css/xe-board-common.css')->load() }}

{{ expose_trans('board::selectPost') }}
{{ expose_trans('board::selectBoard') }}
{{ expose_trans('board::msgDeleteConfirm') }}

<div class="xf-section-board">
    <!-- 게시판 헤더 -->
    <div class="xf-board-header">
        <!-- 상단 타이틀 -->
        @if (request()->segment(2) === null)
            <div class="xf-board-title-wrap xf-mo-mb20 xf-pc-mb08">
                @if (in_array(array_get($skinConfig, 'titleStyle', 'titleWithCount'), ['titleWithCount', 'title']) === true)
                    <div class="xf-board-title-box">
                        <h2 class="xf-board-title xf-heading xf-mr06">
                            <a href="{{ $urlHandler->get('index') }}" class="xf-a">
                                @if (xe_trans($config->get('boardName', '')) !== '')
                                    {{ xe_trans($config->get('boardName')) }}
                                @else
                                    {{ xe_trans(current_menu()['title']) }}
                                @endif
                            </a>
                        </h2>
                        @if (array_get($skinConfig, 'titleStyle', 'titleWithCount') === 'titleWithCount')
                            <span class="xf-board-title-number">({{ number_format($paginate->total()) }})</span>
                        @endif
                    </div>
                @endif
                @if (array_get($skinConfig, 'visibleIndexMobileWriteButton', 'on') === 'on')
                    @if (array_get($skinConfig, 'visibleIndexWriteButton', 'show') === 'show')
                        <a href="{{ $urlHandler->get('create') }}" class="xf-write-btn">
                        </a>
                    @elseif (request()->segment(2) === null && array_get($skinConfig, 'visibleIndexWriteButton', 'show') === 'permission' && $isWritable === true)
                        <div class="xe-list-board-header__write-button">
                            <a href="{{ $urlHandler->get('create') }}" class="xf-write-btn">
                            </a>
                        </div>
                    @endif
                @endif
            </div>
            @if ($config->get('topCommonContentOnlyList') === true)
                <div class="xf-board-common-box xf-pc-mb24">
                    <p class="xf-common-text xf-common-top xf-p">
                        {!! xe_trans($config->get('topCommonContent', '')) !!}
                    </p>
                </div>
            @endif
        @endif
    </div>
    <!-- //게시판 헤더 -->

    <!-- 게시판 본문 -->
    <div class="xf-board-body">
        @section('content')
            {!! isset($content) ? $content : '' !!}
        @show
    </div>
    <!-- //게시판 본문 -->

    <!-- 게시판 푸터 -->
    <div class="xf-board-footer">
        <!-- 하단 공통 내용 -->
        @if ($config->get('bottomCommonContentOnlyList') === true)
            <div class="xf-board-common-box xf-mb40">
                <p class="xf-common-text xf-common-bottom xf-p">
                    {!! xe_trans($config->get('bottomCommonContent', '')) !!}
                </p>
            </div>
    @endif
    <!-- //하단 공통 내용 -->
    </div>
    <!-- //게시판 푸터 -->
</div>
