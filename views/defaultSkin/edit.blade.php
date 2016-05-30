{{ XeFrontend::rule('board', $rules) }}

<div class="board_write">
    <form method="post" id="board_form" class="__board_form" action="{{ $urlHandler->get('update') }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="id" value="{{$item->id}}" />
        <input type="hidden" name="queryString" value="{{ http_build_query(Input::except('parentId')) }}" />
        <div class="write_header">
            <div class="write_category">
                @if($config->get('category') == true)
                    {!! uio('uiobject/board@select', [
                        'name' => 'categoryItemId',
                        'label' => xe_trans('xe::category'),
                        'value' => $item->boardCategory != null ? $item->boardCategory->itemId : '',
                        'items' => $categories,
                    ]) !!}
                @endif
            </div>
            <div class="write_title">
                {!! uio('titleWithSlug', [
                'title' => Input::old('title', $item->title),
                'slug' => $item->getSlug(),
                'titleClassName' => 'bd_input',
                'config' => $config
                ]) !!}
            </div>
        </div>
        <div class="write_body">
            <div class="write_form_editor">
                {!! uio('editor', [
                  'content' => Input::old('content', $item->content),
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

        <div class="dynamic-field">
            @foreach ($configHandler->formColumns($instanceId) as $columnName)
                @if (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) != null)
                    <div class="__xe_{{$columnName}} __xe_section">
                        {!! $fieldType->getSkin()->edit($item->getAttributes()) !!}
                    </div>
                @endif
            @endforeach
        </div>
        <!-- 비로그인 -->
        <div class="write_footer">
            <div class="write_form_input">
                @if ($item->userType == $item::USER_TYPE_GUEST)
                    <div class="xe-form-inline">
                        <input type="text" class="xe-form-control" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Input::old('writer') }}">
                        <input type="password" class="xe-form-control" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}">
                        <input type="email" class="xe-form-control" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Input::old('email') }}">
                    </div>
                @endif
            </div>
            <div class="write_form_option">
                <div class="xe-form-inline">
                    @if($config->get('comment') === true)
                        <label class="xe-label">
                            <input type="checkbox" name="allowComment" value="1" @if($item->boardData->allowComment) checked="checked" @endif>
                            <span class="xe-input-helper"></span>
                            <span class="xe-label-text">{{xe_trans('board::allowComment')}}</span>
                        </label>
                    @endif

                    @if (Auth::check() === true)
                        <label class="xe-label">
                            <input type="checkbox" name="useAlarm" value="1" @if($item->boardData->useAlarm) checked="checked" @endif>
                            <span class="xe-input-helper"></span>
                            <span class="xe-label-text">{{xe_trans('board::useAlarm')}}</span>
                        </label>
                    @endif

                    <label class="xe-label">
                        <input type="checkbox" name="display" value="{{$item::DISPLAY_SECRET}}" @if($item->display == $item::DISPLAY_SECRET) checked="checked" @endif>
                        <span class="xe-input-helper"></span>
                        <span class="xe-label-text">{{xe_trans('board::secretPost')}}</span>
                    </label>

                    @if($isManager === true)
                        <label class="xe-label">
                            <input type="checkbox" name="status" value="{{$item::STATUS_NOTICE}}" @if($item->status == $item::STATUS_NOTICE) checked="checked" @endif>
                            <span class="xe-input-helper"></span>
                            <span class="xe-label-text">{{xe_trans('xe::notice')}}</span>
                        </label>
                    @endif
                </div>
            </div>
            <div class="write_form_btn @if (Auth::guest() === true) nologin @endif">
                {{--<a href="#" class="bd_btn btn_temp_save">임시저장</a>--}}
                <a href="{{ $urlHandler->get('preview') }}" class="bd_btn btn_preview __xe_btn_preview">{{ xe_trans('xe::preview') }}</a>
                <a href="{{ $urlHandler->get('update') }}" class="bd_btn btn_submit __xe_btn_submit">{{ xe_trans('xe::submit') }}</a>
            </div>
        </div>
        <!-- /비로그인 -->

    </form>

</div>