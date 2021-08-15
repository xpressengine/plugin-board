{{ XeFrontend::css('plugins/board/assets/css/xe-board-create.css')->load() }}

{{ XeFrontend::rule('board', $rules) }}

{{ XeFrontend::js('assets/core/common/js/draft.js')->appendTo('head')->load() }}
{{ XeFrontend::css('assets/core/common/css/draft.css')->load() }}

@inject('anonymityHandler', 'Xpressengine\Plugins\Board\AnonymityHandler')

@if($config->get('useTag') === true)
    {{ XeFrontend::js('plugins/board/assets/js/BoardTags.js')->appendTo('body')->load() }}
@endif

<form method="post" id="board_form" class=" __board_form xf-board-writing-form" action="{{ $urlHandler->get('store') }}"
      enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast"
      data-instance_id="{{$instanceId}}" data-url-preview="{{ $urlHandler->get('preview') }}">
    <input type="hidden" name="_token" value="{{{ Session::token() }}}"/>
    <input type="hidden" name="head" value="{{$head}}"/>
    <input type="hidden" name="queryString" value="{{ http_build_query(Request::except('parent_id')) }}"/>

    @foreach ($skinConfig['formColumns'] as $columnName)
        @switch ($columnName)
            @case ('title')
            <div class="xf-write-title-box xf-row">
                @if ($config->get('category') == true)
                    <div class="xf-write-category-box xf-write-dropdown
                                    @if ($config->get('category') === true && $config->get('useTitleHead') == true) xf-col07 xf-pc-pr15
                                    @elseif ($config->get('category') === true || $config->get('useTitleHead') == true) xf-col03 xf-pc-pr15
                                    @else xf-display-none @endif">
                        {!! uio('uiobject/board@new_select', [
                            'name' => 'category_item_id',
                            'label' => xe_trans('xe::category'),
                            'value' => Request::get('category_item_id'),
                            'items' => $categories
                        ]) !!}

                    </div>
                @endif
                @if ($config->get('useTitleHead') === true)
                    <div class="xf-write-title-head-box xf-write-dropdown
                                    @if ($config->get('category') === true && $config->get('useTitleHead') == true) xf-col03
                                    @elseif ($config->get('category') === true || $config->get('useTitleHead') == true) xf-col03 xf-pc-pr15
                                    @else xf-display-none @endif">
                        {!! uio('uiobject/board@new_select', [
                            'name' => 'title_head',
                            'label' => xe_trans('board::titleHead'),
                            'value' => Request::old('title_head'),
                            'items' => $titleHeadItems,
                        ]) !!}
                    </div>
                @endif
                <div class="xf-write-title-input-box
                                    @if ($config->get('category') === true && $config->get('useTitleHead') == true) xf-col10
                                    @elseif ($config->get('category') === true || $config->get('useTitleHead') == true) xf-col07
                                    @else xf-col10 @endif">
                    {!! uio('newTitleWithSlug', [
                        'title' => Request::old('title'),
                        'slug' => Request::old('slug'),
                        'titleClassName' => 'bd_input',
                        'config' => $config
                    ]) !!}
                </div>
            </div>
            @break

            @case('content')
            <div class="xf-write-editor-box">
                {!! editor($config->get('boardId'), [
                'content' => Request::old('content'),
                'cover' => true,
                ]) !!}
            </div>

            <div class="xf-write-tag-box">
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

    <div class="xf-write-footer-box">
        @if (Auth::check() === false)
            <div class="xf-input-nomember-box">
                <input type="text" name="writer" class="xf-nonmember-input"
                       placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}"
                       value="{{ Request::old('writer') }}">
                <input type="password" name="certify_key" class="xf-nonmember-input"
                       placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}"
                       data-valid-name="{{xe_trans('xe::certify_key')}}">
                <input type="email" name="email" class="xf-nonmember-input"
                       placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}"
                       value="{{ Request::old('email') }}">
            </div>
        @endif
        <div class="xf-write-check-box">
            <ul class="xf-list xf-write-check-list">
                @if($config->get('secretPost') === true)
                    <li class="xf-check-item">
                        <input type="checkbox" class="xf-check-item__input"
                               id="xf-check-item__secret" name="display"
                               value="{{\Xpressengine\Document\Models\Document::DISPLAY_SECRET}}">
                        <label class="xf-check-item__label"
                               for="xf-check-item__secret">{{xe_trans('board::secretPost')}}</label>
                    </li>
                @endif

                @if($config->get('comment') === true)
                    <li class="xf-check-item">
                        <input type="checkbox" class="xf-check-item__input"
                               id="xf-check-item__comment" name="allow_comment" value="1"
                               checked="checked">
                        <label class="xf-check-item__label"
                               for="xf-check-item__comment">{{xe_trans('board::allowComment')}}</label>
                    </li>
                @endif

                {{-- anonymity --}}
                @if ($anonymityHandler->isActivatedChoose($config->get('anonymity')))
                    <li class="xf-check-item">
                        <input
                            type="checkbox"
                            class="xf-check-item__input"
                            id="xf-check-item__anonymity"
                            name="allow_anonymity"
                            value="1"
                        >
                        <label class="xf-check-item__label" for="xf-check-item__anonymity">{{xe_trans('board::anonymity')}}</label>
                    </li>
                @endif

                @if (Auth::check() === true)
                    <li class="xf-check-item">
                        <input type="checkbox" class="xf-check-item__input"
                               id="xf-check-item__alarm" name="use_alarm" value="1"
                               @if($config->get('newCommentNotice') === true) checked="checked" @endif>
                        <label class="xf-check-item__label"
                               for="xf-check-item__alarm">{{xe_trans('board::useAlarm')}}</label>
                    </li>
                @endif

                {{-- notice --}}
                @if ($isManager === true && $config->get('noticePost', true))
                    <li class="xf-check-item">
                        <input type="checkbox" class="xf-check-item__input"
                               id="xf-check-item__notice" name="status"
                               value="{{\Xpressengine\Document\Models\Document::STATUS_NOTICE}}">
                        <label class="xf-check-item__label"
                               for="xf-check-item__notice">{{xe_trans('xe::notice')}}</label>
                    </li>
                @endif
            </ul>
        </div>

        <div class="draft_container"></div>

        @if($config['useCaptcha'] === true)
            <div class="captcha_container">
                {!! uio('captcha') !!}
            </div>
        @endif

        <div class="xf-write-btn-box">
            <div class="xf-draft-box xf-write-btn">
                <div class="xf-draft-save-btn">
                    <a href="#" class="xf-board-btn__link xf-board-draft__text xf-a __xe_btn_save"
                       onclick="return false;">
                        <div class="xf-draft-btn">
                            <span class="xf-text">{{ xe_trans('xe::draftSave') }}</span>
                        </div>
                    </a>
                    <a href="#" class="xf-board-btn__link xf-a" onclick="return false;">
                        <div class="xf-more-btn"></div>
                    </a>
                </div>
                <div class="xf-draft-load-btn">
                    <a href="#" class="xf-board-write-btn xf-a __xe_btn_load">{{ xe_trans('xe::draftLoad') }}</a>
                </div>
            </div>
            <div class="xf-preview-btn xf-write-btn">
                <button type="button" class="xf-board-write-btn __xe_btn_preview">{{ xe_trans('xe::preview') }}</button>
            </div>
            <div class="xf-submit-btn xf-write-btn">
                <button type="submit" class="xf-board-write-btn __xe_btn_submit">{{ xe_trans('xe::submit') }}</button>
            </div>
        </div>
    </div>
</form>

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
            btnLoad: $('.__xe_btn_load', form),
            btnSave: $('.__xe_btn_save', form),
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

    $(document).ready(function () {
        $(".xf-draft-save-btn .xf-more-btn").click(function () {
            $(".xf-draft-load-btn").toggleClass("open");
        });
    });
</script>
