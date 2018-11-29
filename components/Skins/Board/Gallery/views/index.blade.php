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
        <a href="#" class="btn_mng bd_sorting"><i class="xi-filter"></i> <span class="xe-sr-only">{{xe_trans('xe::order')}}</span></a>
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

<!-- /검색 정보 출력 -->
@if ($searchOptions != null)
    <div class="xe-row">
        <div class="xe-col-md-12">
            <div class="panel">
                <div class="panel-heading">
                    <h3> {{ xe_trans('board::searchResult', ['count' => $paginate->total()]) }}</h3>
                </div>

                <div class="panel-body">
                    <ul>
                        @foreach ($searchOptions as $name => $key)
                            <li>{{$name}} : {{$key}}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

<!--[D] 한 줄에 노출될 컬럼 수 설정
    3컬럼(기본) : g_col3
    2컬럼 : g_col2
    4컬럼 : g_col4
    5컬럼 : g_col5
-->
<div class="board_list v2 gallery g_col3">
    <ul>
        @foreach($notices as $item)
            <li>
                <div class="thumb_area">
                    <a href="{{$urlHandler->getShow($item, Request::all())}}">
                        <div class="thumbnail-cover thumbnail-cover--scale" @if($item->board_thumbnail_path) style="background-image: url('{{ $item->board_thumbnail_path }}')" @endif></div>
                    </a>
                </div>
                <div class="cont_area">
                    <div class="board_category">
                        <span class="xe-badge xe-primary">{{ xe_trans('xe::notice') }}</span>
                        @if ($config->get('category') == true && $item->boardCategory !== null)
                            <span class="category">{!! xe_trans($item->boardCategory->categoryItem->word) !!}</span>
                        @endif
                    </div>
                    @if ($item->display == $item::DISPLAY_SECRET)
                        <span class="bd_ico_lock"><i class="xi-lock"></i><span class="xe-sr-only">secret</span></span>
                    @endif
                    <a class="title" href="{{$urlHandler->getShow($item, Request::all())}}" id="title_{{$item->id}}">
                        {!! $item->title !!}
                    </a>
                    @if($item->comment_count > 0)
                        <a href="#" class="reply_num xe-hidden-xs" title="Replies">{{ $item->comment_count }}</a>
                    @endif
                    @if ($item->data->fileCount > 0)
                        <span class="bd_ico_file"><i class="xi-paperclip"></i><span class="xe-sr-only">file</span></span>
                    @endif
                    @if($item->isNew($config->get('newTime')))
                        <span class="bd_ico_new"><i class="xi-new"></i><span class="xe-sr-only">new</span></span>
                    @endif

                    <div class="more_info">
                        @if ($isManager === true)
                            <label class="xe-label">
                                <input type="checkbox" title="{{xe_trans('xe::select')}}" class="bd_manage_check" value="{{ $item->id }}">
                                <span class="xe-input-helper"></span>
                                <span class="xe-label-text xe-sr-only">{{xe_trans('xe::select')}}</span>
                            </label>
                        @endif

                        @if (Auth::check() === true)
                            <a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="favorite @if($item->favorite !== null) on @endif __xe-bd-favorite"  title="{{xe_trans('board::favorite')}}"><i class="xi-star"></i><span class="xe-sr-only">{{xe_trans('board::favorite')}}</span></a>
                        @endif

                        <span class="autohr_area">
                            @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                <a href="#" class="mb_autohr"
                                data-toggle="xe-page-toggle-menu"
                                data-url="{{ route('toggleMenuPage') }}"
                                data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>{!! $item->writer !!}</a>
                            @else
                                <a class="mb_autohr">{!! $item->writer !!}</a>
                            @endif
                        </span>
                        <span class="mb_time" title="{{ $item->created_at }}"><i class="xi-time" data-xe-timeago="{{ $item->created_at }}">{{$item->created_at}}</i></span>
                        <span class="mb_read_num"><i class="xi-eye"></i> {{ $item->read_count }}</span>

                    </div>
                </div>
            </li>
        @endforeach

        @foreach($paginate as $item)
            <li>
                <div class="thumb_area">
                    <a href="{{$urlHandler->getShow($item, Request::all())}}">
                        <div class="thumbnail-cover thumbnail-cover--scale" @if($item->board_thumbnail_path) style="background-image: url('{{ $item->board_thumbnail_path }}')" @endif></div>
                    </a>
                </div>
                <div class="cont_area">
                    <div class="board_category">
                        @if ($config->get('category') == true && $item->boardCategory !== null)
                            <span class="category">{!! xe_trans($item->boardCategory->categoryItem->word) !!}</span>
                        @endif
                    </div>

                    @if ($item->display == $item::DISPLAY_SECRET)
                        <span class="bd_ico_lock"><i class="xi-lock"></i><span class="xe-sr-only">secret</span></span>
                    @endif
                    <a class="title" href="{{$urlHandler->getShow($item, Request::all())}}" id="title_{{$item->id}}">
                        {!! $item->title !!}
                    </a>
                    @if($item->comment_count > 0)
                        <a href="#" class="reply_num xe-hidden-xs" title="Replies">{{ $item->comment_count }}</a>
                    @endif
                    @if ($item->data->fileCount > 0)
                        <span class="bd_ico_file"><i class="xi-paperclip"></i><span class="xe-sr-only">file</span></span>
                    @endif
                    @if($item->isNew($config->get('newTime')))
                        <span class="bd_ico_new"><i class="xi-new"></i><span class="xe-sr-only">new</span></span>
                    @endif
                    <div class="more_info">
                        @if ($isManager === true)
                            <label class="xe-label">
                                <input type="checkbox" title="{{xe_trans('xe::select')}}" class="bd_manage_check" value="{{ $item->id }}">
                                <span class="xe-input-helper"></span>
                                <span class="xe-label-text xe-sr-only">{{xe_trans('xe::select')}}</span>
                            </label>
                        @endif

                        @if (Auth::check() === true)
                            <a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="favorite @if($item->favorite !== null) on @endif __xe-bd-favorite"  title="{{xe_trans('board::favorite')}}"><i class="xi-star"></i><span class="xe-sr-only">{{xe_trans('board::favorite')}}</span></a>
                        @endif

                        <span class="autohr_area">
                            @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                <a href="#" class="mb_autohr" data-toggle="xe-page-toggle-menu" data-url="{{ route('toggleMenuPage') }}" data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>{!! $item->writer !!}</a>
                            @else
                                <a class="mb_autohr">{!! $item->writer !!}</a>
                            @endif
                        </span>
                        <span class="mb_time" title="{{ $item->created_at }}"><i class="xi-time" data-xe-timeago="{{ $item->created_at }}">{{$item->created_at}}</i></span>
                        <span class="mb_read_num"><i class="xi-eye"></i> {{ $item->read_count }}</span>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
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
