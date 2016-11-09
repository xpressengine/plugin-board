{{ XeFrontend::rule('board', $rules) }}
{{ XeFrontend::js('plugins/board/assets/js/BoardTags.js')->appendTo('body')->load() }}

<div class="board_write">
    <form method="post" id="board_form" class="__board_form" action="{{ $urlHandler->get('store') }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast" data-instanceId="{{$instanceId}}" data-url-preview="{{ $urlHandler->get('preview') }}">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="head" value="{{$head}}" />
        <input type="hidden" name="queryString" value="{{ http_build_query(Input::except('parentId')) }}" />

        @foreach ($skinConfig['formColumns'] as $columnName)
            @if($columnName === 'title')
                <div class="write_header">
                    <div class="write_category">
                        @if($config->get('category') == true)
                            {!! uio('uiobject/board@select', [
                                'name' => 'categoryItemId',
                                'label' => xe_trans('xe::category'),
                                'value' => '',
                                'items' => $categories,
                            ]) !!}
                        @endif
                    </div>
                    <div class="write_title">
                        {!! uio('titleWithSlug', [
                        'title' => Input::old('title'),
                        'slug' => Input::old('slug'),
                        'titleClassName' => 'bd_input',
                        'config' => $config
                        ]) !!}
                    </div>
                </div>
            @elseif($columnName === 'content')
                <div class="write_body">
                    <div class="write_form_editor">
                        {!! editor($config->get('boardId'), [
                          'content' => Input::old('content'),
                        ]) !!}
                    </div>
                </div>

                @if($config->get('useTag') === true)
                    <div id="xeBoardTagWrap" class="xe-select-label __xe-board-tag" data-placeholder="{{xe_trans('board::inputTag')}}" data-url="/editor/hashTag" data-tags=""></div>
                @endif
            @else
                <div class="__xe_{{$columnName}} __xe_section">
                    {!! dfCreate($config->get('documentGroup'), $columnName, Input::all()) !!}
                </div>
            @endif
        @endforeach

    <div class="dynamic-field">
        @foreach ($configHandler->getDynamicFields($config) as $dynamicFieldConfig)
            @if (in_array($dynamicFieldConfig->get('id'), $skinConfig['formColumns']) === false && ($fieldType = XeDynamicField::getByConfig($dynamicFieldConfig)) != null)
                <div class="__xe_{{$dynamicFieldConfig->get('id')}} __xe_section">
                    {!! dfCreate($dynamicFieldConfig->get('group'), $dynamicFieldConfig->get('id'), Input::all()) !!}
                </div>
            @endif
        @endforeach
    </div>

    <!-- 비로그인 -->
    <div class="write_footer">
        <div class="write_form_input">
            @if (Auth::check() === false)
            <div class="xe-form-inline">
                <input type="text" name="writer" class="xe-form-control" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Input::old('writer') }}">
                <input type="password" name="certifyKey" class="xe-form-control" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}">
                <input type="email" name="email" class="xe-form-control" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Input::old('email') }}">
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
                @if($config->get('comment') === true)
                <label class="xe-label">
                    <input type="checkbox" name="allowComment" value="1" checked="checked">
                    <span class="xe-input-helper"></span>
                    <span class="xe-label-text">{{xe_trans('board::allowComment')}}</span>
                </label>
                @endif

                @if (Auth::check() === true)
                <label class="xe-label">
                    <input type="checkbox" name="useAlarm" value="1">
                    <span class="xe-input-helper"></span>
                    <span class="xe-label-text">{{xe_trans('board::useAlarm')}}</span>
                </label>
                @endif

                <label class="xe-label">
                    <input type="checkbox" name="display" value="{{\Xpressengine\Document\Models\Document::DISPLAY_SECRET}}">
                    <span class="xe-input-helper"></span>
                    <span class="xe-label-text">{{xe_trans('board::secretPost')}}</span>
                </label>

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
            {{--<a href="#" class="bd_btn btn_temp_save __xe_temp_btn_save">임시저장</a>--}}
            <a href="#" class="bd_btn btn_preview __xe_btn_preview">{{ xe_trans('xe::preview') }}</a>
            <a href="#" class="bd_btn btn_submit __xe_btn_submit">{{ xe_trans('xe::submit') }}</a>
        </div>
    </div>
    </form>
</div>

<style type="text/css">
    /* Example Styles for React Tags*/
    div.ReactTags__tags {
        position: relative;
    }

    /* Styles for the input */
    div.ReactTags__tagInput {
        width: 200px;
        border-radius: 2px;
        display: inline-block;
    }
    div.ReactTags__tagInput input,
    div.ReactTags__tagInput input:focus {
        height: 31px;
        margin: 0;
        font-size: 12px;
        width: 100%;
        border: 1px solid #eee;
    }

    /* Styles for selected tags */
    div.ReactTags__selected span.ReactTags__tag {
        border: 1px solid #ddd;
        background: #eee;
        font-size: 12px;
        display: inline-block;
        padding: 5px;
        margin: 0 5px;
        /*cursor: move;*/
        border-radius: 2px;
    }
    div.ReactTags__selected a.ReactTags__remove {
        color: #aaa;
        margin-left: 5px;
        cursor: pointer;
    }

    /* Styles for suggestions */
    div.ReactTags__suggestions {
        position: absolute;
    }
    div.ReactTags__suggestions ul {
        list-style-type: none;
        box-shadow: .05em .01em .5em rgba(0,0,0,.2);
        background: white;
        width: 200px;
    }
    div.ReactTags__suggestions li {
        border-bottom: 1px solid #ddd;
        padding: 5px 10px;
        margin: 0;
    }
    div.ReactTags__suggestions li mark {
        text-decoration: underline;
        background: none;
        font-weight: 600;
    }
    div.ReactTags__suggestions ul li.active {
        background: #b7cfe0;
        cursor: pointer;
    }

</style>