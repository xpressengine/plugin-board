{{ XeFrontend::css('plugins/board/assets/css/new-board-create.css')->load() }}

{{ XeFrontend::rule('board', $rules) }}

{{ XeFrontend::js('assets/core/common/js/draft.js')->appendTo('head')->load() }}
{{ XeFrontend::css('assets/core/common/css/draft.css')->load() }}

@if($config->get('useTag') === true)
{{ XeFrontend::js('plugins/board/assets/js/BoardTags.js')->appendTo('body')->load() }}
@endif

<div class="xe-list-board-body">
    <form method="post" id="board_form" class="row __board_form" action="{{ $urlHandler->get('update', app('request')->query->all()) }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast" data-instance_id="{{$item->instance_id}}" data-url-preview="{{ $urlHandler->get('preview') }}">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="id" value="{{$item->id}}" />
        <input type="hidden" name="queryString" value="{{ http_build_query(Request::except('parent_id')) }}" />

        @foreach ($skinConfig['formColumns'] as $columnName)
            @switch ($columnName)
                @case ('title')
                <div class="xe-list-board-body--header xf-row">
                    <div class="xe-list-board-body--header-item xe-list-board-body--header-title @if ($config->get('category') === true || $config->get('useTitleHead') == true) xf-col-md-8 @else xf-col-md-12 @endif">
                        {!! uio('newTitleWithSlug', [
                            'title' => Request::old('title', $item->title),
                            'slug' => $item->getSlug(),
                            'titleClassName' => 'bd_input',
                            'config' => $config
                        ]) !!}
                    </div>
                    @if($config->get('category') === true || $config->get('useTitleHead') == true)
                        <div class="pdl20 xe-list-board-body--header-item xe-list-board-body--header-select xf-col-md-4">
                            @if ($config->get('category') === true)
                                {!! uio('uiobject/board@new_select', [
                                    'name' => 'category_item_id',
                                    'label' => xe_trans('xe::category'),
                                    'value' => $item->boardCategory != null ? $item->boardCategory->item_id : '',
                                    'items' => $categories
                                ]) !!}
                            @endif
                            @if ($config->get('useTitleHead') == true)
                                {!! uio('uiobject/board@new_select', [
                                'name' => 'title_head',
                                'label' => xe_trans('board::titleHead'),
                                'value' => Request::old('title_head', $item->data->title_head),
                                'items' => $titleHeadItems,
                                ]) !!}
                            @endif
                        </div>
                    @endif
                </div>
                @break

                @case('content')
                <div class="xe-list-board-body--editor">
                    {!! editor($config->get('boardId'), [
                        'content' => Request::old('content', $item->content),
                        'cover' => true,
                    ], $item->id, $thumb ? $thumb->board_thumbnail_file_id : null ) !!}
                </div>

                <div class="xe-list-board-body--tag">
                    @if($config->get('useTag') === true)
                        {!! uio('uiobject/board@tag', [
                            'tags' => $item->tags->toArray(),
                            'placeholder' => '태그를 입력한 후 Enter를 누르세요'
                        ]) !!}
                    @endif
                </div>
                @break

                @default
                @if (isset($dynamicFieldsById[$columnName]) && $dynamicFieldsById[$columnName]->get('use') === true)
                    <div class="__xe_{{$columnName}} __xe_section">
                        {!! df_edit($config->get('documentGroup'), $columnName, $item->getAttributes()) !!}
                    </div>
                @endif
                @break
            @endswitch
        @endforeach

        <div class="xe-list-board-body--footer">
            <div class="xe-list-board-body--footer-additional-box">
                @if ($item->user_type == $item::USER_TYPE_GUEST)
                    <div class="xe-list-board-body--footer-nonmember">
                        <h4 class="xe-list-board-body--footer-title blind">비회원</h4>
                        <input type="text" name="writer" class="xe-list-board-body--footer-nonmember-input" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Request::old('writer', $item->writer) }}">
                        <input type="password" name="certify_key" class="xe-list-board-body--footer-nonmember-input" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}" data-valid-name="{{xe_trans('xe::certify_key')}}">
                        <input type="email" name="email" class="xe-list-board-body--footer-nonmember-input" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Request::old('email', $item->email) }}">
                    </div>
                @endif

                <div class="xe-list-board-body--footer-check-box">
                    <ul class="xe-list-board-body--footer-check-list">
                        @if($config->get('secretPost') === true)
                            <li class="xe-list-board-body--footer-check-item">
                                <input type="checkbox" class="xe-list-board-body--footer-check-item-input" id="xe-list-board-body--footer-check-item-secret" name="display" value="{{ $item::DISPLAY_SECRET }}" @if($item->display == $item::DISPLAY_SECRET) checked="checked" @endif>
                                <label class="xe-list-board-body--footer-check-item-text" for="xe-list-board-body--footer-check-item-secret">{{xe_trans('board::secretPost')}}</label>
                            </li>
                        @endif

                        @if($config->get('comment') === true)
                            <li class="xe-list-board-body--footer-check-item">
                                <input type="checkbox" class="xe-list-board-body--footer-check-item-input" id="xe-list-board-body--footer-check-item-comment" name="allow_comment" value="1" @if ($item->boardData->allow_comment == 1) checked="checked" @endif>
                                <label class="xe-list-board-body--footer-check-item-text" for="xe-list-board-body--footer-check-item-comment">{{xe_trans('board::allowComment')}}</label>
                            </li>
                        @endif

                        @if (Auth::check() === true)
                            <li class="xe-list-board-body--footer-check-item">
                                <input type="checkbox" class="xe-list-board-body--footer-check-item-input" id="xe-list-board-body--footer-check-item-alarm" name="use_alarm" value="1" @if($item->boardData->use_alarm == 1) checked="checked" @endif>
                                <label class="xe-list-board-body--footer-check-item-text" for="xe-list-board-body--footer-check-item-alarm">{{xe_trans('board::useAlarm')}}</label>
                            </li>
                        @endif

                        @if ($isManager === true)
                            <li class="xe-list-board-body--footer-check-item">
                                <input type="checkbox" class="xe-list-board-body--footer-check-item-input" id="xe-list-board-body--footer-check-item-notice" name="status" value="{{$item::STATUS_NOTICE}}" @if($item->status == $item::STATUS_NOTICE) checked="checked" @endif>
                                <label class="xe-list-board-body--footer-check-item-text" for="xe-list-board-body--footer-check-item-notice">{{xe_trans('xe::notice')}}</label>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="draft_container"></div>

            @if($config['useCaptcha'] === true)
                <div class="captcha_container">
                    {!! uio('captcha') !!}
                </div>
            @endif

            <div class="xe-list-board-body--footer-button-box">
                <div class="xe-list-board-body--footer-button">
                    <div class="xe-list-board-body--footer-button__transient">
                        <a href="#" class="xe-list-board-body--footer-button__draftsave __xe_temp_btn_save" onclick="return false;">{{ xe_trans('xe::draftSave') }}</a>

                        <a href="#" class="xe-list-board-body--footer-button__draftload-arrow"  onclick="return false;">
                            <i class="xi-angle-down-min"></i>
                        </a>
                    </div>
                    <div class="xe-list-board-body--footer-button__transient-content">
                        <a href="#" class="xe-list-board-body--footer-button__draftload xe-list-board-body--footer-button__transient-content __xe_temp_btn_load transient-content-edit">{{ xe_trans('xe::draftLoad') }}</a>
                    </div>
                </div>
                <button type="button" class="xe-list-board-body--footer-button xe-list-board-body--footer-button__preview __xe_btn_preview">{{ xe_trans('xe::preview') }}</button>
                <button type="submit" class="xe-list-board-body--footer-button xe-list-board-body--footer-button__register __xe_btn_submit">{{ xe_trans('xe::submit') }}</button>
            </div>
        </div>
    </form>
</div>

<script>
    $(function () {
        var form = $('.__board_form');
        var submitting = false
        form.on('submit', function (e) {
            if (submitting) {
                return false
            }

            if (!submitting) {
                form.find('[type=submit]').prop('disabled', true)
                submitting = true
                setTimeout(function () {
                    form.find('[type=submit]').prop('disabled', false)
                }, 5000);
            }
        })

        var draft = $('#xeContentEditor', form).draft({
            key: 'document|' + form.data('instance_id'),
            btnLoad: $('.__xe_temp_btn_load', form),
            btnSave: $('.__xe_temp_btn_save', form),
            // container: $('.draft_container', form),
            withForm: true,
            apiUrl: {
                draft: {
                    add: xeBaseURL + '/draft/store',
                    update: xeBaseURL + '/draft/update',
                    delete: xeBaseURL + '/draft/destroy',
                    list: xeBaseURL + '/draft',
                },
                auto: {
                    set: xeBaseURL + '/draft/setAuto',
                    unset: xeBaseURL + '/draft/destroyAuto'
                }
            },
            callback: function (data) {
                window.XE.app('Editor').then(function (appEditor) {
                    appEditor.getEditor('XEckeditor').then(function (editorDefine) {
                        var inst = editorDefine.editorList['xeContentEditor']
                        if (inst) {
                            inst.setContents(data.content);
                        }
                    })
                })
            }
        });
    });

    $(document).ready(function(){
        $(".xe-list-board-body--footer-button__draftload-arrow").click(function(){
            $(".xe-list-board-body--footer-button__transient-content").toggleClass("open");
        });
    });
</script>
