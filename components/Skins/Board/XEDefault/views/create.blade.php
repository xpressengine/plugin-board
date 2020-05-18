{{ XeFrontend::css('plugins/board/assets/css/new-board-create.css')->load() }}

{{ XeFrontend::rule('board', $rules) }}

{{ XeFrontend::js('assets/core/common/js/draft.js')->appendTo('head')->load() }}
{{ XeFrontend::css('assets/core/common/css/draft.css')->load() }}

@if($config->get('useTag') === true)
{{ XeFrontend::js('plugins/board/assets/js/BoardTags.js')->appendTo('body')->load() }}
@endif

<div class="xe-list-board-body">
    <form method="post" id="board_form" class="row __board_form" action="{{ $urlHandler->get('store') }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast" data-instance_id="{{$instanceId}}" data-url-preview="{{ $urlHandler->get('preview') }}">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="head" value="{{$head}}" />
        <input type="hidden" name="queryString" value="{{ http_build_query(Request::except('parent_id')) }}" />
        
        @foreach ($skinConfig['formColumns'] as $columnName)
            @switch ($columnName)
                @case ('title')
                    <div class="xe-list-board-body--header xf-row">
                        <div class="xe-list-board-body--header-item xe-list-board-body--header-title @if ($config->get('category') === true) xf-col-md-8 @else xf-col-md-12 @endif">
                            {!! uio('newTitleWithSlug', [
                                'title' => Request::old('title'),
                                'slug' => Request::old('slug'),
                                'titleClassName' => 'bd_input',
                                'config' => $config
                            ]) !!}
                        </div>
                        @if($config->get('category') === true)
                            <div class="pdl20 xe-list-board-body--header-item xe-list-board-body--header-select xf-col-md-4">
                                {!! uio('uiobject/board@new_select', [
                                    'name' => 'category_item_id',
                                    'label' => xe_trans('xe::category'),
                                    'value' => Request::get('category_item_id'),
                                    'items' => $categories
                                ]) !!}
                            </div>
                        @endif
                    </div>
                    @break
                
                    @case('content')
                        <div class="xe-list-board-body--editor">
                            {!! editor($config->get('boardId'), [
                            'content' => Request::old('content'),
                            'cover' => true,
                            ]) !!}
                        </div>

                        <div class="xe-list-board-body--tag">
                            @if($config->get('useTag') === true)
                                {!! uio('uiobject/board@tag', [
                                    'placeholder' => '태그를 입력한 후 Enter를 누르세요'
                                ]) !!}
                            @endif
                        </div>
                        @break
                
                    @default
                        @if (isset($dynamicFieldsById[$columnName]) && $dynamicFieldsById[$columnName]->get('use') === true)
                            <div class="__xe_{{$columnName}} __xe_section">
                                {!! df_create($config->get('documentGroup'), $columnName, Request::all()) !!}
                            </div>
                        @endif
                        @break
            @endswitch
        @endforeach
        
        <div class="xe-list-board-body--footer">
            <div class="xe-list-board-body--footer-additional-box">
                @if (Auth::check() === false)
                    <div class="xe-list-board-body--footer-nonmember">
                        <h4 class="xe-list-board-body--footer-title blind">비회원</h4>
                        <input type="text" name="writer" class="xe-list-board-body--footer-nonmember-input" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Request::old('writer') }}">
                        <input type="password" name="certify_key" class="xe-list-board-body--footer-nonmember-input" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}" data-valid-name="{{xe_trans('xe::certify_key')}}">
                        <input type="email" name="email" class="xe-list-board-body--footer-nonmember-input" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Request::old('email') }}">
                    </div>
                @endif
                
                <div class="xe-list-board-body--footer-check-box">
                    <ul class="xe-list-board-body--footer-check-list">
                        @if($config->get('secretPost') === true)
                            <li class="xe-list-board-body--footer-check-item">
                                <input type="checkbox" class="xe-list-board-body--footer-check-item-input" id="xe-list-board-body--footer-check-item-secret" name="display" value="{{\Xpressengine\Document\Models\Document::DISPLAY_SECRET}}">
                                <label class="xe-list-board-body--footer-check-item-text" for="xe-list-board-body--footer-check-item-secret">{{xe_trans('board::secretPost')}}</label>
                            </li>
                        @endif

                        @if($config->get('comment') === true)
                            <li class="xe-list-board-body--footer-check-item">
                                <input type="checkbox" class="xe-list-board-body--footer-check-item-input" id="xe-list-board-body--footer-check-item-comment" name="allow_comment" value="1" checked="checked">
                                <label class="xe-list-board-body--footer-check-item-text" for="xe-list-board-body--footer-check-item-comment">{{xe_trans('board::allowComment')}}</label>
                            </li>
                        @endif

                        @if (Auth::check() === true)
                            <li class="xe-list-board-body--footer-check-item">
                                <input type="checkbox" class="xe-list-board-body--footer-check-item-input" id="xe-list-board-body--footer-check-item-alarm" name="use_alarm" value="1" @if($config->get('newCommentNotice') === true) checked="checked" @endif>
                                <label class="xe-list-board-body--footer-check-item-text" for="xe-list-board-body--footer-check-item-alarm">{{xe_trans('board::useAlarm')}}</label>
                            </li>
                        @endif
                        
                        @if ($isManager === true)
                            <li class="xe-list-board-body--footer-check-item">
                                <input type="checkbox" class="xe-list-board-body--footer-check-item-input" id="xe-list-board-body--footer-check-item-notice" name="status" value="{{\Xpressengine\Document\Models\Document::STATUS_NOTICE}}">
                                <label class="xe-list-board-body--footer-check-item-text" for="xe-list-board-body--footer-check-item-notice">{{xe_trans('xe::notice')}}</label>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
            
            <div class="draft_container"></div>
            <div class="captcha_container">
                @if($config['useCaptcha'] === true)
                    {!! uio('captcha') !!}
                @endif
            </div>

            
            <div class="xe-list-board-body--footer-button-box">
                <div class="xe-list-board-body--footer-button">
                    <div class="xe-list-board-body--footer-button__transient __xe_temp_btn_save">
                        <a href="#" class="xe-list-board-body--footer-button__draftsave">{{ xe_trans('xe::draftSave') }}</a>
                        
                        <a href="#" class="xe-list-board-body--footer-button__draftload-arrow">
                            <i class="xi-angle-down-min"></i>
                        </a>
                    </div>
                    <div class="xe-list-board-body--footer-button__transient-content">
                        <a href="#" class="xe-list-board-body--footer-button__draftload __xe_temp_btn_load">{{ xe_trans('xe::draftLoad') }}</a>
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
