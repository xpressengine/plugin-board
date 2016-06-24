{{ XeFrontend::rule('board', $rules) }}

<div class="board_write">
    <form method="post" id="board_form" class="__board_form" action="{{ $urlHandler->get('store') }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast" data-instanceId="{{$instanceId}}" data-url-preview="{{ $urlHandler->get('preview') }}">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="parentId" value="{{$parentId}}" />
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
                        {{--<div class="temp_save">--}}
                            {{--<a href="#" class="temp_save_num" data-toggle="xe-modal" data-target="#Modal"><strong>3</strong>개의 임시 저장 글</a>--}}
                        {{--</div>--}}
                        {!! uio('titleWithSlug', [
                        'title' => Input::old('title'),
                        'slug' => '',
                        'titleClassName' => 'bd_input',
                        'config' => $config
                        ]) !!}
                    </div>
                </div>
            @elseif($columnName === 'content')
                <div class="write_body">
                    <div class="write_form_editor">
                        {!! uio('editor', [
                          'content' => Input::old('content'),
                          'editorConfig' => [
                            'fileUpload' => [
                              'upload_url' => $urlHandler->get('upload'),
                              'source_url' => $urlHandler->get('source'),
                              'download_url' => $urlHandler->get('download'),
                            ],
                            'suggestion' => [
                              'hashtag_api' => $urlHandler->get('hashTag'),
                              'mention_api' => $urlHandler->get('mention'),
                            ],
                          ]
                        ]) !!}
                    </div>
                </div>
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
            <a href="#" class="bd_btn btn_temp_save __xe_temp_btn_save">임시저장</a>
            <a href="#" class="bd_btn btn_preview __xe_btn_preview">{{ xe_trans('xe::preview') }}</a>
            <a href="#" class="bd_btn btn_submit __xe_btn_submit">{{ xe_trans('xe::submit') }}</a>
        </div>
    </div>
    </form>
</div>