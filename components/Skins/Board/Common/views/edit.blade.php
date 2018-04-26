{{ XeFrontend::rule('board', $rules) }}

@if($config->get('useTag') === true)
{{ XeFrontend::js('plugins/board/assets/js/BoardTags.js')->appendTo('body')->load() }}
@endif

<div class="board_write">
    <form method="post" id="board_form" class="__board_form" action="{{ $urlHandler->get('update', app('request')->query->all()) }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast" data-instance_id="{{$item->instance_id}}" data-url-preview="{{ $urlHandler->get('preview') }}">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="id" value="{{$item->id}}" />
        <input type="hidden" name="queryString" value="{{ http_build_query(Request::except('parent_id')) }}" />
        @foreach ($skinConfig['formColumns'] as $columnName)
            @if($columnName === 'title')
                <div class="write_header">
                    <div class="write_category">
                        @if($config->get('category') == true)
                            {!! uio('uiobject/board@select', [
                                'name' => 'category_item_id',
                                'label' => xe_trans('xe::category'),
                                'value' => $item->boardCategory != null ? $item->boardCategory->item_id : '',
                                'items' => $categories,
                            ]) !!}
                        @endif
                    </div>
                    <div class="write_title">
                        {!! uio('titleWithSlug', [
                        'title' => Request::old('title', $item->title),
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
                          'content' => Request::old('content', $item->content),
                          'cover' => true,
                        ], $item->id, $thumb->board_thumbnail_file_id) !!}
                    </div>
                </div>

                @if($config->get('useTag') === true)
                    {!! uio('uiobject/board@tag', [
                    'tags' => $item->tags->toArray()
                    ]) !!}
                @endif
            @else
                @if(isset($dynamicFieldsById[$columnName]) && $dynamicFieldsById[$columnName]->get('use') == true)
                <div class="__xe_{{$columnName}} __xe_section">
                    {!! df_edit($config->get('documentGroup'), $columnName, $item->getAttributes()) !!}
                </div>
                @endif
            @endif
        @endforeach

        <div class="dynamic-field">
            @foreach ($fieldTypes as $dynamicFieldConfig)
                @if (in_array($dynamicFieldConfig->get('id'), $skinConfig['formColumns']) === false && ($fieldType = XeDynamicField::getByConfig($dynamicFieldConfig)) != null && $dynamicFieldConfig->get('use') == true)
                    <div class="__xe_{{$dynamicFieldConfig->get('id')}} __xe_section">
                        {!! $fieldType->getSkin()->edit($item->getAttributes()) !!}
                    </div>
                @endif
            @endforeach
        </div>

        <div class="write_footer">
            <div class="write_form_input">
                @if ($item->user_type == $item::USER_TYPE_GUEST)
                    <div class="xe-form-inline">
                        <input type="text" name="writer" class="xe-form-control" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Request::old('writer', $item->writer) }}">
                        <input type="password" name="certify_key" class="xe-form-control" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}">
                        <input type="email" name="email" class="xe-form-control" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Request::old('email', $item->email) }}">
                    </div>
                @endif
            </div>
            <div class="write_form_option">
                <div class="xe-form-inline">
                    @if($config->get('comment') === true)
                        <label class="xe-label">
                            <input type="checkbox" name="allow_comment" value="1" @if($item->boardData->allow_comment == 1) checked="checked" @endif>
                            <span class="xe-input-helper"></span>
                            <span class="xe-label-text">{{xe_trans('board::allowComment')}}</span>
                        </label>
                    @endif

                    @if (Auth::check() === true)
                        <label class="xe-label">
                            <input type="checkbox" name="use_alarm" value="1" @if($item->boardData->use_alarm == 1) checked="checked" @endif>
                            <span class="xe-input-helper"></span>
                            <span class="xe-label-text">{{xe_trans('board::useAlarm')}}</span>
                        </label>
                    @endif

                    @if($config->get('secretPost') === true)
                    <label class="xe-label">
                        <input type="checkbox" name="display" value="{{$item::DISPLAY_SECRET}}" @if($item->display == $item::DISPLAY_SECRET) checked="checked" @endif>
                        <span class="xe-input-helper"></span>
                        <span class="xe-label-text">{{xe_trans('board::secretPost')}}</span>
                    </label>
                    @endif

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
                <button type="button" href="#" class="xe-btn xe-btn-normal bd_btn btn_preview __xe_btn_preview">{{ xe_trans('xe::preview') }}</button>
                <button type="submit" href="#" class="xe-btn xe-btn-primary bd_btn btn_submit __xe_btn_submit">{{ xe_trans('xe::submit') }}</button>
            </div>
        </div>
    </form>
</div>
