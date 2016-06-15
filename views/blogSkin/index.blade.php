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
        <a href="#" class="btn_mng bd_sorting"><i class="xi-funnel"></i> <span class="xe-sr-only">게시글 정렬</span></a>
    </div>
    <!-- /모바일뷰에서 노출되는 정렬 버튼 -->

    <div class="bd_btn_area">
        <ul>
            <!-- [D] 클릭시 클래스 on 및 추가 bd_search_area 영역 활성화 -->
            <li><a href="#" class="bd_search __xe-bd-search"><span class="xe-sr-only">{{ xe_trans('xe::search') }}</span><i class="xi-magnifier"></i></a></li>
            <li><a href="{{ $urlHandler->get('create') }}"><span class="xe-sr-only">{{ xe_trans('board::newPost') }}</span><i class="xi-pen-o"></i></a></li>
            @if ($isManager === true)
                <li><a href="{{ route('manage.board.board.edit', ['boardId'=>$instanceId]) }}" target="_blank"><span class="xe-sr-only">{{ xe_trans('xe::manage') }}</span><i class="xi-cog"></i></a></li>
            @endif
        </ul>
    </div>
    <div class="xe-form-inline xe-hidden-xs __xe-forms">
        @if($config->get('category') == true)
            {!! uio('uiobject/board@select', [
                'name' => 'categoryItemId',
                'label' => xe_trans('xe::category'),
                'value' => Input::get('categoryItemId'),
                'items' => $categories,
            ]) !!}
        @endif

        {!! uio('uiobject/board@select', [
            'name' => 'orderType',
            'label' => xe_trans('xe::order'),
            'value' => Input::get('orderType'),
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
                            <button type="button" class="xe-btn xe-btn-primary-outline __xe_btn_submit" data-href="{{ $urlHandler->managerUrl('copy') }}">{{ xe_trans('xe::copy') }}</button>
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
                            <button type="button" class="xe-btn xe-btn-primary-outline __xe_btn_submit" data-href="{{ $urlHandler->managerUrl('move') }}">{{ xe_trans('xe::move') }}</button>
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
                        <a href="{{ $urlHandler->managerUrl('trash') }}" class="xe-btn-link __xe_btn_submit">{{ xe_trans('board::postsMoveToTrash') }}</a>
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
                        <a href="{{ $urlHandler->managerUrl('destroy') }}" class="xe-btn-link __xe_btn_submit">{{ xe_trans('board::postsDelete') }}</a>
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
                <input type="text" name="title_pureContent" class="bd_search_input" title="{{ xe_trans('board::boardSearch') }}" placeholder="{{ xe_trans('xe::enterKeyword') }}" value="{{ Input::get('title_pureContent') }}">
                <!-- [D] 클릭시 클래스 on 및 추가 bd_search_detail 영역 활성화 -->
                <a href="#" class="bd_btn_detail" title="{{ xe_trans('board::boardDetailSearch') }}">{{ xe_trans('board::detailSearch') }}</a>
            </div>
        </form>
        <form method="get" class="__xe_search" action="{{ $urlHandler->get('index') }}">
            <input type="hidden" name="orderType" value="{{ input::get('orderType') }}" />
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
                                            'name' => 'categoryItemId',
                                            'label' => xe_trans('xe::category'),
                                            'value' => Input::get('categoryItemId'),
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
                                    <input type="text" name="title_pureContent" class="xe-form-control" title="{{ xe_trans('board::titleAndContent') }}" value="{{ Input::get('title_pureContent') }}">
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
                                    <input type="text" name="writer" class="xe-form-control" title="{{ xe_trans('xe::writer') }}" value="{{ Input::get('writer') }}">
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
                                            'value' => Input::get('period'),
                                            'items' => $terms,
                                        ]) !!}
                                    </div>
                                    <div class="xe-form-inline">
                                        <input type="text" name="startCreatedAt" class="xe-form-control" title="{{xe_trans('board::startDate')}}" value="{{Input::get('startCreatedAt')}}"> - <input type="text" name="endCreatedAt" class="xe-form-control" title="{{xe_trans('board::endDate')}}" value="{{Input::get('endCreatedAt')}}">
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
                            {!! XeDynamicField::get($config->get('documentGroup'), $typeConfig->get('id'))->getSkin()->search(Input::all()) !!}
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

<div class="board_list v2 blog">
    <ul>
        @foreach($paginate as $item)
            <li>
                <div class="title_area">
                    @if ($config->get('category') == true && $item->boardCategory !== null)
                        <span class="category">{!! $item->boardCategory->categoryItem->word !!}</span>
                    @endif
                    <a class="title" href="{{$urlHandler->getShow($item, Input::all())}}" id="title_{{$item->id}}">{!! $item->title !!}</a>
                </div>
                <div class="thumb_area">
                    <a href="#">
                        <img src="{{ $item->boardThumbnailPath }}" alt="">
                        @if($item->isNew($config->get('newTime')))
                            <span class="ribbon new"><span class="bd_hidden">new</span></span>
                        @endif
                    </a>
                </div>
                <div class="cont_area">
                    <p>{!! mb_substr($item->pureContent, 0, 100) !!}</p>
                    <div class="more_info">
                        <input type="checkbox" title="체크" class="bd_manage_check" value="{{ $item->id }}">
                        <a href="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="favorite @if($item->favorite !== null) on @endif __xe-bd-favorite"  title="{{xe_trans('board::favorite')}}"><i class="xi-star"></i><span class="xe-sr-only">{{xe_trans('board::favorite')}}</span></a>
                            <span class="autohr_area">
                                <a href="#" class="mb_autohr __xe_user" data-id="{{$item->userId}}">{!! $item->writer !!}</a>
                            </span>
                        @if (in_array('createdAt', $config->get('listColumns')) == true)
                            <span class="mb_time"><i class="xi-time"></i> <span data-xe-timeago="{{ $item->createdAt }}">{{$item->createdAt}}</span></span>
                        @endif
                        @if (in_array('readCount', $config->get('listColumns')) == true)
                            <span class="mb_read_num"><i class="xi-eye"></i> {{ $item->readCount }}</span>
                        @endif
                        <a href="#" class="mb_reply_num"><i class="xi-comment"></i> {{ $item->commentCount }}</a>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>

<div class="board_footer">
    <!-- PAGINATAION PC-->
    {!! $paginationPresenter->render() !!}
    <!-- /PAGINATION PC-->

    <!-- PAGINATAION Mobile -->
    {!! $paginationMobilePresenter->render() !!}
    <!-- /PAGINATION Mobile -->
</div>
<div class="bd_dimmed"></div>