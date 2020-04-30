{{ XeFrontend::rule('board', $rules) }}

{{ XeFrontend::js('assets/core/common/js/draft.js')->appendTo('head')->load() }}
{{ XeFrontend::css('assets/core/common/css/draft.css')->load() }}

@if($config->get('useTag') === true)
{{ XeFrontend::js('plugins/board/assets/js/BoardTags.js')->appendTo('body')->load() }}
@endif

<div class="board_write">
    <form method="post" id="board_form" class="__board_form" action="{{ $urlHandler->get('store') }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast" data-instance_id="{{$instanceId}}" data-url-preview="{{ $urlHandler->get('preview') }}">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="head" value="{{$head}}" />
        <input type="hidden" name="queryString" value="{{ http_build_query(Request::except('parent_id')) }}" />

        @foreach ($skinConfig['formColumns'] as $columnName)
        @if($columnName === 'title')
        <div class="write_header">
            <div class="write_title">
                {!! uio('titleWithSlug', [
                'title' => Request::old('title'),
                'slug' => Request::old('slug'),
                'titleClassName' => 'bd_input',
                'config' => $config
                ]) !!}
            </div>
            <div class="write_category">
                @if($config->get('category') == true)
                {!! uio('uiobject/board@select', [
                'name' => 'category_item_id',
                'label' => xe_trans('xe::category'),
                'value' => Request::get('category_item_id'),
                'items' => $categories,
                ]) !!}
                @endif
            </div>
        </div>
        @elseif($columnName === 'content')
        <div class="write_body">
            <div class="write_form_editor">
                {!! editor($config->get('boardId'), [
                'content' => Request::old('content'),
                'cover' => true,
                ]) !!}
            </div>
        </div>

        @if($config->get('useTag') === true)
        {!! uio('uiobject/board@tag') !!}
        @endif
        @else
        @if(isset($dynamicFieldsById[$columnName]) && $dynamicFieldsById[$columnName]->get('use') == true)
        <div class="__xe_{{$columnName}} __xe_section">
            {!! df_create($config->get('documentGroup'), $columnName, Request::all()) !!}
        </div>
        @endif
        @endif
        @endforeach

        <div class="dynamic-field">
            @foreach ($fieldTypes as $dynamicFieldConfig)
            @if (in_array($dynamicFieldConfig->get('id'), $skinConfig['formColumns']) === false && ($fieldType = XeDynamicField::getByConfig($dynamicFieldConfig)) != null && $dynamicFieldConfig->get('use') == true)
            <div class="__xe_{{$dynamicFieldConfig->get('id')}} __xe_section">
                {!! df_create($dynamicFieldConfig->get('group'), $dynamicFieldConfig->get('id'), Request::all()) !!}
            </div>
            @endif
            @endforeach
        </div>

        <div class="draft_container"></div>

        <!-- 비로그인 -->
        <div class="write_footer">
            <div class="write_form_input">
                @if (Auth::check() === false)
                <div class="xe-form-inline">
                    <input type="text" name="writer" class="xe-form-control" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Request::old('writer') }}">
                    <input type="password" name="certify_key" class="xe-form-control" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}" data-valid-name="{{xe_trans('xe::certify_key')}}">
                    <input type="email" name="email" class="xe-form-control" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Request::old('email') }}">
                </div>
                @endif
            </div>

            @if($config['useCaptcha'] === true)
            <div class="write_form_input">
                {!! uio('captcha') !!}
            </div>
            @endif

            <div class="write_form_option">
                <div class="xe-form-inline">

                    @if($config->get('secretPost') === true)
                    <label class="xe-label">
                        <input type="checkbox" name="display" value="{{\Xpressengine\Document\Models\Document::DISPLAY_SECRET}}">
                        <span class="xe-input-helper"></span>
                        <span class="xe-label-text">{{xe_trans('board::secretPost')}}</span>
                    </label>
                    @endif

                    @if($config->get('comment') === true)
                    <label class="xe-label">
                        <input type="checkbox" name="allow_comment" value="1" checked="checked">
                        <span class="xe-input-helper"></span>
                        <span class="xe-label-text">{{xe_trans('board::allowComment')}}</span>
                    </label>
                    @endif
                    
                    @if (Auth::check() === true)
                    <label class="xe-label">
                        <input type="checkbox" name="use_alarm" value="1" @if($config->get('newCommentNotice') == true) checked="checked" @endif >
                        <span class="xe-input-helper"></span>
                        <span class="xe-label-text">{{xe_trans('board::useAlarm')}}</span>
                    </label>
                    @endif

                    @if($isManager === true)
                    <label class="xe-label">
                        <input type="checkbox" name="status" value="{{\Xpressengine\Document\Models\Document::STATUS_NOTICE}}">
                        <span class="xe-input-helper"></span>
                        <span class="xe-label-text">{{xe_trans('xe::notice')}}</span>
                    </label>
                    @endif
                </div>
            </div>
            <div class="write_form_btn @if (Auth::check() === false) nologin @endif">
                <!-- Split button -->
                <span class="xe-btn-group">
                    <button type="button" class="xe-btn xe-btn-secondary __xe_temp_btn_save">{{ xe_trans('xe::draftSave') }}</button>
                    <button type="button" class="xe-btn xe-btn-secondary xe-dropdown-toggle" data-toggle="xe-dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="xe-sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="xe-dropdown-menu">
                        <li><a href="#" class="__xe_temp_btn_load">{{ xe_trans('xe::draftLoad') }}</a></li>
                    </ul>
                </span>
                <button type="button" class="xe-btn xe-btn-normal bd_btn btn_preview __xe_btn_preview">{{ xe_trans('xe::preview') }}</button>
                <button type="submit" class="xe-btn bd_btn btn_submit __xe_btn_submit">{{ xe_trans('xe::submit') }}</button>
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
</script>
