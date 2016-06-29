{{ XeFrontend::rule('board', $rules) }}

<div class="board_write">
    <form method="post" id="board_form" class="__board_form" action="{{ $urlHandler->get('update') }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="id" value="{{$item->id}}" />
        <input type="hidden" name="queryString" value="{{ http_build_query(Input::except('parentId')) }}" />
        @foreach ($skinConfig['formColumns'] as $columnName)
            @if($columnName === 'title')
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
            @elseif($columnName === 'content')
                <div class="write_body">
                    <div class="write_form_editor">
                        {!! editor($config->get('boardId'), [
                          'content' => Input::old('content', $item->content),
                        ], $item->id) !!}
                    </div>
                </div>
            @else
                <div class="__xe_{{$columnName}} __xe_section">
                    {!! dfEdit($config->get('documentGroup'), $columnName, $item->getAttributes()) !!}
                </div>
            @endif
        @endforeach

        <div class="dynamic-field">
            @foreach ($configHandler->getDynamicFields($config) as $dynamicFieldConfig)
                @if (in_array($dynamicFieldConfig->get('id'), $skinConfig['formColumns']) === false && ($fieldType = XeDynamicField::getByConfig($dynamicFieldConfig)) != null)
                    <div class="__xe_{{$dynamicFieldConfig->get('id')}} __xe_section">
                        {!! $fieldType->getSkin()->edit($item->getAttributes()) !!}
                    </div>
                @endif
            @endforeach
        </div>

        <div class="write_footer">
            <div class="write_form_input">
                @if ($item->userType == $item::USER_TYPE_GUEST)
                    <div class="xe-form-inline">
                        <input type="text" name="writer" class="xe-form-control" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Input::old('writer', $item->writer) }}">
                        <input type="password" name="certifyKey" class="xe-form-control" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}">
                        <input type="email" name="email" class="xe-form-control" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Input::old('email', $item->email) }}">
                    </div>
                @endif
            </div>
            <div class="write_form_option">
                <div class="xe-form-inline">
                    @if($config->get('comment') === true)
                        <label class="xe-label">
                            <input type="checkbox" name="allowComment" value="1" @if($item->boardData->allowComment == 1) checked="checked" @endif>
                            <span class="xe-input-helper"></span>
                            <span class="xe-label-text">{{xe_trans('board::allowComment')}}</span>
                        </label>
                    @endif

                    @if (Auth::check() === true)
                        <label class="xe-label">
                            <input type="checkbox" name="useAlarm" value="1" @if($item->boardData->useAlarm == 1) checked="checked" @endif>
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
            <div class="write_form_btn @if (Auth::check() === false) nologin @endif">
                {{--<a href="#" class="bd_btn btn_temp_save">임시저장</a>--}}
                <a href="{{ $urlHandler->get('preview') }}" class="bd_btn btn_preview __xe_btn_preview">{{ xe_trans('xe::preview') }}</a>
                <a href="{{ $urlHandler->get('update') }}" class="bd_btn btn_submit __xe_btn_submit">{{ xe_trans('xe::submit') }}</a>
            </div>
        </div>

    </form>

</div>