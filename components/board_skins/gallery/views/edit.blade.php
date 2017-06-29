{{ XeFrontend::css('plugins/board/components/board_skins/gallery/assets/css/skin.css')->load() }}

{{ XeFrontend::js('plugins/board/assets/js/build/board.js')->appendTo('body')->load() }}

{{ XeFrontend::rule('board', $rules) }}

{{ XeFrontend::js('plugins/board/assets/js/build/BoardTags.js')->appendTo('body')->load() }}

<div class="board">
    <div class="board_write">
        <form method="post" class="__board_form" action="{{ $urlHandler->get('update') }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast" data-instanceId="{{$item->instanceId}}" data-url-preview="{{ $urlHandler->get('preview') }}">
            <fieldset>
                <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
                <input type="hidden" name="id" value="{{$item->id}}" />
                <input type="hidden" name="queryString" value="{{ http_build_query(Input::except('parentId')) }}" />

                <div class="write_header">
                    @if($config->get('category') == true)
                        <div>
                            <label>{{xe_trans('xe::category')}}</label>
                            <select name="categoryItemId">
                                <option value="">{{xe_trans('xe::select')}}</option>
                                @foreach ($categories as $category)
                                    <option value="{{$category['value']}}" @if($item->boardCategory == $category['value']) selected="selected" @endif >{{xe_trans($category['text'])}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        {!! uio('titleWithSlug', [
                        'title' => Input::old('title', $item->title),
                        'slug' => $item->getSlug(),
                        'titleClassName' => 'bd_input',
                        'config' => $config
                        ]) !!}
                    </div>
                </div>

                <div class="write_body">
                    {!! editor($config->get('boardId'), [
                      'content' => Input::old('content', $item->content),
                    ], $item->id) !!}

                    @if($config->get('useTag') === true)
                        {!! uio('uiobject/board@tag', [
                        'tags' => $item->tags->toArray()
                        ]) !!}
                    @endif
                </div>

                <div class="write_dynamicField">
                    @foreach ($configHandler->getDynamicFields($config) as $dynamicFieldConfig)
                        {!! XeDynamicField::getByConfig($dynamicFieldConfig)->getSkin()->edit($item->getAttributes()) !!}
                    @endforeach
                </div>

                <div class="write_footer">
                    @if ($item->userType == $item::USER_TYPE_GUEST)
                        <div>
                            <input type="text" name="writer" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Input::old('writer', $item->writer) }}">
                            <input type="password" name="certifyKey" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}">
                            <input type="email" name="email" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Input::old('email', $item->email) }}">
                        </div>
                    @endif

                    @if($config->get('comment') === true)
                        <div>
                            <label>
                                <input type="checkbox" name="allowComment" value="1" @if($item->boardData->allowComment == 1) checked="checked" @endif>
                                <span>{{xe_trans('board::allowComment')}}</span>
                            </label>
                        </div>
                    @endif

                    @if (Auth::check() === true)
                        <div>
                            <label>
                                <input type="checkbox" name="useAlarm" value="1" @if($item->boardData->useAlarm == 1) checked="checked" @endif>
                                <span>{{xe_trans('board::useAlarm')}}</span>
                            </label>
                        </div>
                    @endif

                    <div>
                        <label>
                            <input type="checkbox" name="display" value="{{$item::DISPLAY_SECRET}}" @if($item->display == $item::DISPLAY_SECRET) checked="checked" @endif>
                            <span>{{xe_trans('board::secretPost')}}</span>
                        </label>
                    </div>

                    @if($isManager === true)
                        <div>
                            <label class="xe-label">
                                <input type="checkbox" name="status" value="{{$item::STATUS_NOTICE}}" @if($item->status == $item::STATUS_NOTICE) checked="checked" @endif>
                                <span>{{xe_trans('xe::notice')}}</span>
                            </label>
                        </div>
                    @endif
                </div>

                <div class="@if (Auth::check() === false) nologin @endif">
                    <button type="submit" class="__xe_btn_preview">{{ xe_trans('xe::preview') }}</button>
                    <button type="submit" class="__xe_btn_submit">{{ xe_trans('xe::submit') }}</button>
                </div>
            </fieldset>
        </form>
    </div>
</div>
