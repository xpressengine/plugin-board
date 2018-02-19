{{ XeFrontend::js('assets/core/xe-ui-component/js/xe-page.js')->appendTo('body')->load() }}
<div class="board_header">
    @if ($isManager === true)
    <div class="bd_manage_area">
        <!-- [D] 클릭시 클래스 on 추가 및 bd_manage_detail 영역 노출 -->
        <button type="button" class="xe-btn xe-btn-primary-outline bd_manage __xe-bd-manage">{{ xe_trans('xe::manage') }}</button>
    </div>
    @endif

                <!-- 모바일뷰에서 노출되는 정렬 버튼 -->
    <div class="bd_manage_area xe-visible-xs">
        <!-- [D] 클릭시 클래스 on 추가 및 bd_align 영역 노출 -->
        <a href="#" class="btn_mng bd_sorting __xe-bd-mobile-sorting"><i class="xi-filter"></i> <span class="xe-sr-only">{{xe_trans('xe::order')}}</span></a>
    </div>
    <!-- /모바일뷰에서 노출되는 정렬 버튼 -->

    <div class="bd_btn_area">
        <ul>
            <!-- [D] 클릭시 클래스 on 및 추가 bd_search_area 영역 활성화 -->
            <li><a href="#" class="bd_search __xe-bd-search"><span class="xe-sr-only">{{ xe_trans('xe::search') }}</span><i class="xi-search"></i></a></li>
            <li><a href="{{ $urlHandler->get('create') }}"><span class="xe-sr-only">{{ xe_trans('board::newPost') }}</span><i class="xi-pen-o"></i></a></li>
            @if ($isManager === true)
            <li><a href="{{ $urlHandler->managerUrl('config', ['boardId'=>$instanceId]) }}" target="_blank"><span class="xe-sr-only">{{ xe_trans('xe::manage') }}</span><i class="xi-cog"></i></a></li>
            @endif
        </ul>
    </div>
    <div class="xe-form-inline xe-hidden-xs board-sorting-area __xe-forms">
        @if($config->get('category') == true)
        {!! uio('uiobject/board@select', [
            'name' => 'category_item_id',
            'label' => xe_trans('xe::category'),
            'value' => Request::get('category_item_id'),
            'items' => $categories,
        ]) !!}
        @endif

        {!! uio('uiobject/board@select', [
            'name' => 'order_type',
            'label' => xe_trans('xe::order'),
            'value' => Request::get('order_type'),
            'items' => $handler->getOrders(),
        ]) !!}
    </div>

    <!-- 게시글 관리 -->
    @if ($isManager === true)
    <div class="bd_manage_detail">
        <div class="xe-row">
            <div class="xe-col-sm-6">
                <div class="xe-row __xe_copy">
                    <div class="xe-col-sm-3">
                        <label class="xe-control-label">{{ xe_trans('xe::copy') }}</label>
                    </div>
                    <div class="xe-col-sm-9">
                        <div class="xe-form-inline">
                            {!! uio('uiobject/board@select', [
                                'name' => 'copyTo',
                                'label' => xe_trans('xe::select'),
                                'items' => $boardList,
                            ]) !!}
                            <button type="button" class="xe-btn xe-btn-primary-outline __xe_btn_submit" data-url="{{ $urlHandler->managerUrl('copy') }}">{{ xe_trans('xe::copy') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="xe-row">
            <div class="xe-col-sm-6">
                <div class="xe-row __xe_move">
                    <div class="xe-col-sm-3">
                        <label class="xe-control-label">{{ xe_trans('xe::move') }}</label>
                    </div>
                    <div class="xe-col-sm-9">
                        <div class="xe-form-inline">
                            {!! uio('uiobject/board@select', [
                                'name' => 'moveTo',
                                'label' => xe_trans('xe::select'),
                                'items' => $boardList,
                            ]) !!}
                            <button type="button" class="xe-btn xe-btn-primary-outline __xe_btn_submit" data-url="{{ $urlHandler->managerUrl('move') }}">{{ xe_trans('xe::move') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="xe-row">
            <div class="xe-col-sm-6">
                <div class="xe-row __xe_to_trash">
                    <div class="xe-col-sm-3">
                        <label class="xe-control-label">{{ xe_trans('xe::trash') }}</label>
                    </div>
                    <div class="xe-col-sm-9">
                        <a href="#" data-url="{{ $urlHandler->managerUrl('trash') }}" class="xe-btn-link __xe_btn_submit">{{ xe_trans('board::postsMoveToTrash') }}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="xe-row">
            <div class="xe-col-sm-6">
                <div class="xe-row __xe_delete">
                    <div class="xe-col-sm-3">
                        <label class="xe-control-label">{{ xe_trans('xe::delete') }}</label>
                    </div>
                    <div class="xe-col-sm-9">
                        <a href="#" data-url="{{ $urlHandler->managerUrl('destroy') }}" class="xe-btn-link __xe_btn_submit">{{ xe_trans('board::postsDelete') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- /게시글 관리 -->

    <!-- 검색영역 -->
    <div class="bd_search_area">
        <form method="get" class="__xe_simple_search" action="{{ $urlHandler->get('index') }}">
        <div class="bd_search_box">
            <input type="text" name="title_pure_content" class="bd_search_input" title="{{ xe_trans('board::boardSearch') }}" placeholder="{{ xe_trans('xe::enterKeyword') }}" value="{{ Request::get('title_pure_content') }}">
            <!-- [D] 클릭시 클래스 on 및 추가 bd_search_detail 영역 활성화 -->
            <a href="#" class="bd_btn_detail" title="{{ xe_trans('board::boardDetailSearch') }}">{{ xe_trans('board::detailSearch') }}</a>
        </div>
        </form>
        <form method="get" class="__xe_search" action="{{ $urlHandler->get('index') }}">
            <input type="hidden" name="order_type" value="{{ Request::get('order_type') }}" />
        <div class="bd_search_detail">
            <div class="bd_search_detail_option">
                <div class="xe-row">
                    @if($config->get('category') == true)
                    <div class="xe-col-sm-6">
                        <div class="xe-row">
                            <div class="xe-col-sm-3">
                                <label class="xe-control-label">{{ xe_trans('xe::category') }}</label>
                            </div>
                            <div class="xe-col-sm-9">
                                    {!! uio('uiobject/board@select', [
                                        'name' => 'category_item_id',
                                        'label' => xe_trans('xe::category'),
                                        'value' => Request::get('category_item_id'),
                                        'items' => $categories,
                                    ]) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="xe-col-sm-6">
                        <div class="xe-row">
                            <div class="xe-col-sm-3">
                                <label class="xe-control-label">{{ xe_trans('board::titleAndContent') }}</label>
                            </div>
                            <div class="xe-col-sm-9">
                                <input type="text" name="title_pure_content" class="xe-form-control" title="{{ xe_trans('board::titleAndContent') }}" value="{{ Request::get('title_pure_content') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="xe-row">
                    <div class="xe-col-sm-6">
                        <div class="xe-row">
                            <div class="xe-col-sm-3">
                                <label class="xe-control-label">{{ xe_trans('xe::writer') }}</label>
                            </div>
                            <div class="xe-col-sm-9">
                                <input type="text" name="writer" class="xe-form-control" title="{{ xe_trans('xe::writer') }}" value="{{ Request::get('writer') }}">
                            </div>
                        </div>
                    </div>
                    <div class="xe-col-sm-6">
                        <div class="xe-row __xe-period">
                            <div class="xe-col-sm-3">
                                <label class="xe-control-label">{{xe_trans('board::period')}}</label>
                            </div>
                            <div class="xe-col-sm-9">
                                <div class="xe-form-group">
                                    {!! uio('uiobject/board@select', [
                                        'name' => 'period',
                                        'label' => xe_trans('xe::select'),
                                        'value' => Request::get('period'),
                                        'items' => $terms,
                                    ]) !!}
                                </div>
                                <div class="xe-form-inline">
                                    <input type="text" name="start_created_at" class="xe-form-control" title="{{xe_trans('board::startDate')}}" value="{{Request::get('start_created_at')}}"> - <input type="text" name="end_created_at" class="xe-form-control" title="{{xe_trans('board::endDate')}}" value="{{Request::get('end_created_at')}}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 확장 필드 검색 -->
                @foreach($fieldTypes as $typeConfig)
                    @if($typeConfig->get('searchable') === true)
                <div class="xe-row">
                    <div class="xe-col-sm-3">
                        <label class="xe-control-label">{{ xe_trans($typeConfig->get('label')) }}</label>
                    </div>
                    <div class="xe-col-sm-9">
                        {!! XeDynamicField::get($config->get('documentGroup'), $typeConfig->get('id'))->getSkin()->search(Request::all()) !!}
                    </div>
                </div>
                    @endif
                @endforeach
                <!-- /확장 필드 검색 -->

            </div>
            <div class="bd_search_footer">
                <div class="xe-pull-right">
                    <button type="submit" class="xe-btn xe-btn-primary-outline bd_btn_search">{{ xe_trans('xe::search') }}</button>
                    <button type="button" class="xe-btn xe-btn-secondary bd_btn_cancel">{{ xe_trans('xe::cancel') }}</button>
                </div>
            </div>
        </div>
        </form>
    </div>
    <!-- /검색영역 -->

</div>

<div class="board_list">
    <table>
        <!-- [D] 모바일뷰에서 숨겨할 요소 클래스 xe-hidden-xs 추가 -->
        <thead class="xe-hidden-xs">
        <!-- LIST HEADER -->
        <tr>
            @if ($isManager === true)
            <th scope="col">
                <label class="xe-label">
                    <input type="checkbox" title="{{ xe_trans('xe::checkAll') }}" class="bd_btn_manage_check_all">
                    <span class="xe-input-helper"></span>
                    <span class="xe-label-text xe-sr-only">{{ xe_trans('xe::checkAll') }}</span>
                </label>
            </th>
            @endif

            @foreach ($skinConfig['listColumns'] as $columnName)
                @if ($columnName == 'favorite')
                    @if(Request::has('favorite'))
                        <th scope="col" class="favorite"><span><a href="{{$urlHandler->get('index', Request::except(['favorite', 'page']))}}"><i class="xi-star-o on"></i><span class="xe-sr-only">{{ xe_trans('board::favorite') }}</span></a></span></th>
                    @else
                        <th scope="col" class="favorite"><span><a href="{{$urlHandler->get('index', array_merge(Request::except('page'), ['favorite' => 1]))}}"><i class="xi-star-o"></i><span class="xe-sr-only">{{ xe_trans('board::favorite') }}</span></a></span></th>
                    @endif
                @elseif ($columnName == 'title')
                    @if ($config->get('category') == true)
                        <th scope="col" class="column-th-category"><span>{{ xe_trans('board::category') }}</span></th>
                    @endif
                    <th scope="col" class="title column-th-{{$columnName}}"><span>{{ xe_trans('board::title') }}</span></th>
                @else
                    @if (isset($dynamicFieldsById[$columnName]))
                        <th scope="col" class="column-th-{{$columnName}}"><span>{{ xe_trans($dynamicFieldsById[$columnName]->get('label')) }}</span></th>
                    @else
                        <th scope="col" class="column-th-{{$columnName}}"><span>{{ xe_trans('board::'.$columnName) }}</span></th>
                    @endif
                @endif
            @endforeach
        </tr>
        <!-- /LIST HEADER -->
        </thead>
        <tbody>
        <!-- NOTICE -->
        @foreach($notices as $item)
        <tr class="notice">
            @if ($isManager === true)
            <td class="check">
                <label class="xe-label">
                    <input type="checkbox" title="{{xe_trans('xe::select')}}" class="bd_manage_check" value="{{ $item->id }}">
                    <span class="xe-input-helper"></span>
                    <span class="xe-label-text xe-sr-only">{{xe_trans('xe::select')}}</span>
                </label>
            </td>
            @endif
            @foreach ($skinConfig['listColumns'] as $columnName)
                @if ($columnName == 'favorite')
                        <td class="favorite xe-hidden-xs"><a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="@if($item->favorite !== null) on @endif __xe-bd-favorite"  title="{{xe_trans('board::favorite')}}"><i class="xi-star"></i><span class="xe-sr-only">{{xe_trans('board::favorite')}}</span></a></td>
                @elseif ($columnName == 'title')
                    @if ($config->get('category') == true)
                        <td class="category xe-hidden-xs column-category">{!! $item->boardCategory !== null ? xe_trans($item->boardCategory->categoryItem->word) : '' !!}</td>
                    @endif
                        <td class="title column-{{$columnName}}">
                            <span class="xe-badge xe-primary">{{ xe_trans('xe::notice') }}</span>
                            @if ($item->display == $item::DISPLAY_SECRET)
                                <span class="bd_ico_lock"><i class="xi-lock"></i><span class="xe-sr-only">secret</span></span>
                            @endif
                            <a href="{{$urlHandler->getShow($item, Request::all())}}" id="{{$columnName}}_{{$item->id}}" class="title_text">{!! $item->title !!}</a>
                            @if($item->comment_count > 0)
                                <a href="#" class="reply_num xe-hidden-xs" title="Replies">{{ $item->comment_count }}</a>
                            @endif
                            @if ($item->data->file_count > 0)
                                <span class="bd_ico_file"><i class="xi-paperclip"></i><span class="xe-sr-only">file</span></span>
                            @endif
                            @if($item->isNew($config->get('newTime')))
                                <span class="bd_ico_new"><i class="xi-new"></i><span class="xe-sr-only">new</span></span>
                            @endif
                            <div class="more_info xe-visible-xs">
                                @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                <a href="#" class="mb_author"
                                   data-toggle="xe-page-toggle-menu"
                                   data-url="{{ route('toggleMenuPage') }}"
                                   data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>{!! $item->writer !!}</a>
                                @else
                                    <a class="mb_author">{!! $item->writer !!}</a>
                                @endif
                                <span class="mb_time" title="{{$item->created_at}}"><i class="xi-time"></i> <span @if($item->created_at->getTimestamp() > strtotime('-1 month')) data-xe-timeago="{{ $item->created_at }}" @endif >{{ $item->created_at->toDateString() }}</span></span>
                                <span class="mb_readnum"><i class="xi-eye"></i> {{ $item->read_count }}</span>
                                <a href="#" class="mb_reply_num"><i class="xi-comment"></i> {{ $item->comment_count }}</a>
                            </div>
                        </td>
                @elseif ($columnName == 'writer')
                    <td class="author xe-hidden-xs">
                        @if ($item->hasAuthor() && $config->get('anonymity') === false)
                            <a href="#"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{ route('toggleMenuPage') }}"
                               data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>{!! $item->writer !!}</a>
                        @else
                            <a>{!! $item->writer !!}</a>
                        @endif
                    </td>
                @elseif ($columnName == 'read_count')
                    <td class="read_num xe-hidden-xs">{{ $item->{$columnName} }}</td>
                @elseif (in_array($columnName, ['created_at', 'updated_at', 'deleted_at']))
                    <td class="time xe-hidden-xs column-{{$columnName}}" title="{{ $item->{$columnName} }}" @if($item->{$columnName}->getTimestamp() > strtotime('-1 month')) data-xe-timeago="{{ $item->{$columnName} }}" @endif >{{ $item->{$columnName}->toDateString() }}</td>
                @elseif (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) != null)
                    <td class="xe-hidden-xs column-{{$columnName}}">{!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}</td>
                @else
                    <td class="xe-hidden-xs column-{{$columnName}}">{!! $item->{$columnName} !!}</td>
                @endif
            @endforeach
        </tr>
        @endforeach
        <!-- /NOTICE -->

        @if (count($paginate) == 0)
                <!-- NO ARTICLE -->
        <tr class="no_article">
            <!-- [D] 컬럼수에 따라 colspan 적용 -->
            <td colspan="{{ count($skinConfig['listColumns']) + 2 }}">
                <img src="{{ asset('plugins/board/assets/img/img_pen.jpg') }}" alt="">
                <p>{{ xe_trans('xe::noPost') }}</p>
            </td>
        </tr>
        <!-- / NO ARTICLE -->
        @endif

        <!-- LIST -->
        @foreach($paginate as $item)
        <tr>
            @if ($isManager === true)
            <td class="check">
                <label class="xe-label">
                    <input type="checkbox" title="{{xe_trans('xe::select')}}" class="bd_manage_check" value="{{ $item->id }}">
                    <span class="xe-input-helper"></span>
                    <span class="xe-label-text xe-sr-only">{{xe_trans('xe::select')}}</span>
                </label>
            </td>
            @endif

            @foreach ($skinConfig['listColumns'] as $columnName)
                @if ($columnName == 'favorite')
                        <td class="favorite xe-hidden-xs"><a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="@if($item->favorite !== null) on @endif __xe-bd-favorite"  title="{{xe_trans('board::favorite')}}"><i class="xi-star"></i><span class="xe-sr-only">{{xe_trans('board::favorite')}}</span></a></td>
                @elseif ($columnName == 'title')
                    @if ($config->get('category') == true)
                        <td class="category xe-hidden-xs column-category">{!! $item->boardCategory !== null ? xe_trans($item->boardCategory->categoryItem->word) : '' !!}</td>
                    @endif
                    <td class="title column-{{$columnName}}">
                        @if ($item->display == $item::DISPLAY_SECRET)
                            <span class="bd_ico_lock"><i class="xi-lock"></i><span class="xe-sr-only">secret</span></span>
                        @endif
                        <a href="{{$urlHandler->getShow($item, Request::all())}}" id="{{$columnName}}_{{$item->id}}" class="title_text">{!! $item->title !!}</a>
                        @if($item->comment_count > 0)
                            <a href="#" class="reply_num xe-hidden-xs" title="Replies">{{ $item->comment_count }}</a>
                        @endif
                        @if ($item->data->file_count > 0)
                            <span class="bd_ico_file"><i class="xi-paperclip"></i><span class="xe-sr-only">file</span></span>
                        @endif
                        @if($item->isNew($config->get('newTime')))
                            <span class="bd_ico_new"><i class="xi-new"></i><span class="xe-sr-only">new</span></span>
                        @endif
                        <div class="more_info xe-visible-xs">
                            @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                <a href="#" class="mb_author"
                                   data-toggle="xe-page-toggle-menu"
                                   data-url="{{ route('toggleMenuPage') }}"
                                   data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>{!! $item->writer !!}</a>
                            @else
                                <a class="mb_author">{!! $item->writer !!}</a>
                            @endif
                            <span class="mb_time" title="{{$item->created_at}}"><i class="xi-time"></i> <span @if($item->created_at->getTimestamp() > strtotime('-1 month')) data-xe-timeago="{{ $item->created_at }}" @endif >{{ $item->created_at->toDateString() }}</span></span>
                            <span class="mb_readnum"><i class="xi-eye"></i> {{ $item->read_count }}</span>
                            <a href="#" class="mb_reply_num"><i class="xi-comment"></i> {{ $item->comment_count }}</a>
                        </div>
                    </td>
                @elseif ($columnName == 'writer')
                    <td class="author xe-hidden-xs">
                        @if ($item->hasAuthor() && $config->get('anonymity') === false)
                            <a href="#"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{ route('toggleMenuPage') }}"
                               data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>{!! $item->writer !!}</a>
                        @else
                            <a>{!! $item->writer !!}</a>
                        @endif
                    </td>
                @elseif ($columnName == 'read_count')
                    <td class="read_num xe-hidden-xs">{{ $item->{$columnName} }}</td>
                @elseif (in_array($columnName, ['created_at', 'updated_at', 'deleted_at']))
                    <td class="time xe-hidden-xs column-{{$columnName}}" title="{{ $item->{$columnName} }}" @if($item->{$columnName}->getTimestamp() > strtotime('-1 month')) data-xe-timeago="{{ $item->{$columnName} }}" @endif >{{ $item->{$columnName}->toDateString() }}</td>
                @elseif (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) != null)
                    <td class="xe-hidden-xs column-{{$columnName}}">{!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}</td>
                @else
                    <td class="xe-hidden-xs column-{{$columnName}}">{!! $item->{$columnName} !!}</td>
                @endif
            @endforeach
        </tr>
        @endforeach
        <!-- /LIST -->
        </tbody>
    </table>
</div>

<div class="board_footer">
    <!-- PAGINATAION PC-->
    {!! $paginate->render('board::components.Skins.Board.Common.views.default-pagination') !!}
    <!-- /PAGINATION PC-->

    <!-- PAGINATAION Mobile -->
    {!! $paginate->render('board::components.Skins.Board.Common.views.simple-pagination') !!}
    <!-- /PAGINATION Mobile -->
</div>
<div class="bd_dimmed"></div>
