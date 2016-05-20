{{ XeFrontend::css('plugins/board/assets/css/board.css')->load() }}

<div class="board">
    <div class="board_header">
        @if ($isManager === true)
        <div class="bd_manage_area">
            <!-- [D] 클릭시 클래스 on 추가 -->
            <a href="#" class="btn_mng bd_manage __xe_select_area_show" data-selector=".bd_manage_detail">
                <i class="xi-minus-square-o __xe_select_area_show" data-selector=".bd_manage_detail"></i> <span class="bd_hidden">{{ xe_trans('xe::manage') }}</span>
            </a>
        </div>
        @endif

        <!-- 모바일뷰에서 노출되는 정렬 버튼 -->
        <div class="bd_select_area bd_manage pc_hidden">
            <!-- [D] 클릭시 클래스 on 추가 및 bd_align 영역 노출 -->
        </div>
        <!-- /모바일뷰에서 노출되는 정렬 버튼 -->

        <div class="bd_btn_area">
            <ul>
                <!-- [D] 클릭시 클래스 on 및 추가 bd_search_area 영역 활성화 -->
                <li><a href="#" class="bd_search __xe_select_area_show" data-selector=".bd_search_area"><span class="bd_hidden">{{ xe_trans('xe::search') }}</span>
                        <i class="xi-magnifier __xe_select_area_show" data-selector=".bd_search_area"></i></a>
                </li>
                <li><a href="{{ $urlHandler->get('create') }}"><span class="bd_hidden">{{ xe_trans('board::newPost') }}</span><i class="xi-pen"></i></a></li>
                @if ($isManager === true)
                <li><a href="{{ route('manage.board.board.edit', ['boardId'=>$instanceId]) }}" target="_blank"><span class="bd_hidden">{{ xe_trans('xe::manage') }}</span><i class="xi-cog"></i></a></li>
                @endif
            </ul>
        </div>

        <div class="bd_sorting_area mb_hidden">
            @if($config->get('category') == true)
                <div class="bd_select_area bd_align __xe_category_change">
                    <input type="hidden" name="categoryItemId" value="{{ Input::get('categoryItemId') }}" />
                    <a href="#" class="bd_select __xe_select_box_show">{{ $categoryItem ? xe_trans($categoryItem->word) : xe_trans('xe::category') }}</a>
                    <div class="bd_select_list" data-name="categoryItemId">
                        <ul>
                            <li><a href="#" data-value="">{{xe_trans('xe::category')}}</a></li>
                            @foreach ($categoryItems as $item)
                                <li><a href="#" data-value="{{$item->id}}">{{xe_trans($item->word)}}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="bd_select_area bd_align __xe_order_change">
                {!! uio('uiobject/board@select', [
                    'name' => 'orderType',
                    'label' => xe_trans('xe::order'),
                    'value' => Input::get('orderType'),
                    'items' => $handler->getOrders(),
                ]) !!}
            </div>
        </div>

        <!-- 게시글 관리 -->
        @if ($isManager === true)
        <div class="bd_manage_detail">
            <dl>
                <dt>{{ xe_trans('xe::copy') }}</dt>
                <dd class="__xe_copy">
                    {!! uio('uiobject/board@select', [
                        'name' => 'copyTo',
                        'label' => xe_trans('xe::select'),
                        'items' => $boardList,
                    ]) !!}
                    <a href="{{ $urlHandler->managerUrl('copy') }}" class="btn btn_primary __xe_btn_submit" style="display:none;"><i class="xi-check"></i> {{ xe_trans('xe::copy') }}</a>
                </dd>
                <dt>{{ xe_trans('xe::move') }}</dt>
                <dd class="__xe_move">
                    {!! uio('uiobject/board@select', [
                        'name' => 'moveTo',
                        'label' => xe_trans('xe::select'),
                        'items' => $boardList,
                    ]) !!}
                    <a href="{{ $urlHandler->managerUrl('move') }}" class="btn btn_primary __xe_btn_submit" style="display:none;"><i class="xi-check"></i> {{ xe_trans('xe::move') }}</a>
                </dd>
                <dt>{{ xe_trans('xe::trash') }}</dt>
                <dd class="bd_text __xe_to_trash">
                    <a href="#">{{ xe_trans('board::moveToTrash') }}</a>
                    <a href="{{ $urlHandler->managerUrl('trash') }}" class="btn btn_primary __xe_btn_submit" style="display:none;"><i class="xi-check"></i> {{ xe_trans('xe::move') }}</a>
                </dd>
                <dt>{{ xe_trans('xe::delete') }}</dt>
                <dd class="bd_text __xe_delete">
                    <a href="#">{{ xe_trans('xe::delete') }}</a>
                    <a href="{{ $urlHandler->managerUrl('destroy') }}" class="btn btn_primary __xe_btn_submit" style="display:none;"><i class="xi-check"></i> {{ xe_trans('xe::delete') }}</a>
                </dd>
            </dl>
        </div>
        @endif
        <!-- /게시글 관리 -->

        <!-- 검색영역 -->
        <div class="bd_search_area">
            <form method="get" class="__xe_simple_search" action="{{ $urlHandler->get('index') }}">
            <div class="bd_search_box">
                <input type="text" name="title_pureContent" class="bd_search_input" title="{{ xe_trans('board::board') }} {{ xe_trans('xe::search') }}" placeholder="{{ xe_trans('xe::enterKeyword') }}" value="{{ Input::get('title_pureContent') }}">
                <!-- [D] 클릭시 클래스 on 및 추가 bd_search_detail 영역 활성화 -->
                <a href="#" class="bd_btn_detail __xe_select_area_show" data-selector=".bd_search_detail" title="{{ xe_trans('board::board') }} {{ xe_trans('board::detail') }}"><span class="bd_hidden">{{ xe_trans('board::detail') }}</span></a>
            </div>
            </form>
            <form method="get" class="__xe_search" action="{{ $urlHandler->get('index') }}">
                <input type="hidden" name="categoryItemId" value="{{ Input::get('categoryItemId') }}" />
                <input type="hidden" name="orderType" value="{{ input::get('orderType') }}" />
            <div class="bd_search_detail">
                <dl>
                    <dt>{{ xe_trans('board::titleAndContent') }}</dt>
                    <dd><input type="text" name="title_pureContent" class="bd_input" title="{{ xe_trans('board::titleAndContent') }}" value="{{ Input::get('title_pureContent') }}"></dd>
                    <dt>{{ xe_trans('xe::writer') }}</dt>
                    <dd><input type="text" class="bd_input" title="{{ xe_trans('xe::writer') }}"></dd>
                    <!-- 확장 필드 검색 -->
                    @foreach($fieldTypes as $typeConfig)
                        @if($typeConfig->get('searchable') === true)
                        <dt>{{ xe_trans($typeConfig->get('label')) }}</dt>
                        <dd>
                            {!! XeDynamicField::get($config->get('documentGroup'), $typeConfig->get('id'))->getSkin()->search(Input::all()) !!}
                        </dd>
                        @endif
                    @endforeach
                    <!-- /확장 필드 검색 -->
                </dl>
                <div class="bd_search_footer">
                    <a href="#" class="bd_btn_search"><i class="xi-magnifier"></i><span class="bd_hidden">{{ xe_trans('xe::search') }}</span></a>
                    <a href="#" class="bd_btn_cancel"><i class="xi-close"></i><span class="bd_hidden">{{ xe_trans('xe::cancel') }}</span></a>
                </div>
            </div>
            </form>
        </div>
        <!-- /검색영역 -->

    </div>

    <div class="board_list">
        <table>
            <!-- [D] 모바일뷰에서 숨겨할 요소 클래스 mb_hidden 추가 -->
            <thead class="mb_hidden">
            <!-- LIST HEADER -->
            <tr>
                <th scope="col" style="width:44px"><span><input type="checkbox" title="{{ xe_trans('xe::checkAll') }}" class="bd_btn_manage_check_all"></span></th>
                <th scope="col" class="favorite"><span><a href="{{ $urlHandler->get('index', ['favorite' => '1']) }}" class=" @if(Input::has('favorite') === true) on @endif " title="{{ xe_trans('board::favoriteFilter') }}"><i class="xi-star-o"></i><span class="bd_hidden">{{ xe_trans('board::favoriteFilter') }}</span></a></span></th>

                @foreach ($config->get('listColumns') as $columnName)
                    @if ($columnName == 'title')
                        @if ($config->get('category') == true)
                            <th scope="col" class="title column-th-category"><span>{{ xe_trans('board::category') }}</span></th>
                        @endif
                        <th scope="col" class="title column-th-{{$columnName}}"><span>{{ xe_trans('board::'.$columnName) }}</span></th>
                    @else
                        <th scope="col" class="column-th-{{$columnName}}"><span>{{ xe_trans('board::'.$columnName) }}</span></th>
                    @endif
                @endforeach
            </tr>
            <!-- /LIST HEADER -->
            </thead>
            <tbody>

            <!-- NOTICE -->
            @foreach($handler->getsNotice($config) as $item)
                <tr class="notice">
                    <td class="check"><input type="checkbox" title="{{xe_trans('xe::manage')}}" class="bd_manage_check" value="{{ $item->id }}"></td>
                    <td class="favorite mb_hidden"><a href="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="@if($item->favorite !== null) on @endif xe-favorite" title="{{xe_trans('board::favorite')}}" data-id="{{ $item->id }}"><i class="xi-star"></i><span class="bd_hidden">{{xe_trans('board::favorite')}}</span></a></td>

                    @foreach ($config->get('listColumns') as $columnName)
                        @if ($columnName == 'title')
                            @if ($config->get('category') == true)
                                <td class="category mb_hidden column-category">{!! $item->boardCategory !== null ? $item->boardCategory->categoryItem->word : '' !!}</td>
                            @endif
                            <td class="title column-{{$columnName}}">
                                <span class="category">{{ xe_trans('xe::notice') }}</span>
                                <a href="{{$urlHandler->getShow($item, Input::all())}}" id="{{$columnName}}_{{$item->id}}">{!! $item->title !!}</a>

                                @if($item->commentCount > 0)
                                <span class="reply_num mb_hidden" title="Replies">{{ $item->commentCount }}</span>
                                @endif
                                @if($item->isNew($config->get('newTime')))
                                    <span class="bd_ico_new"><i class="xi-new"></i><span class="bd_hidden">new</span></span>
                                @endif

                                @if ($item->fileCount > 0)
                                    <span class="bd_ico_file" data-count="{{ $item->fileCount }}"><i class="xi-clip"></i><span class="bd_hidden">file</span></span>
                                @endif
                                <div class="more_info pc_hidden">
                                    <a href="#" class="mb_autohr" class="__xe_user" data-id="{{$item->getUserId()}}">{!! $item->writer !!}</a>
                                    <span class="mb_time"><i class="xi-time"></i> {!! sprintf('‘%s', str_replace('-', '.', substr($item->createdAt, 2, 8))) !!}</span>
                                    <span class="mb_readnum"><i class="xi-eye"></i> {{ $item->readCount }}</span>
                                    <a href="#" class="mb_reply_num"><i class="xi-comment"></i> {{ $item->commentCount }}</a>
                                </div>
                            </td>
                        @elseif ($columnName == 'writer')
                            <td class="author mb_hidden column-{{$columnName}}"><a href="#" class="__xe_user" data-id="{{$item->getUserId()}}">{!! $item->writer !!}</a></td>
                        @elseif ($columnName == 'readCount')
                            <td class="read_num mb_hidden">{{ $item->{$columnName} }}</td>
                        @elseif (in_array($columnName, ['createdAt', 'updatedAt', 'deletedAt']))
                            <td class="time mb_hidden column-{{$columnName}}" title="{{ $item->{$columnName} }}">{!! sprintf('‘%s', str_replace('-', '.', substr($item->{$columnName}, 2, 8))) !!}</td>
                        @elseif (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) != null)
                            @if ($columnName == 'category')
                                <td class="category mb_hidden column-{{$columnName}}">{!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}</td>
                            @else
                                <td class="mb_hidden column-{{$columnName}}">{!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}</td>
                            @endif
                        @else
                            <td class="mb_hidden column-{{$columnName}}">{!! $item->{$columnName} !!}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
            <!-- /NOTICE -->

            @if (count($paginate) == 0)
            <!-- NO ARTICLE -->
            <tr class="no_article">
                <!-- [D] 컬럼수에 따라 colspan 적용 -->
                <td colspan="{{ count($config->get('listColumns')) + 1 }}">
                    <img src="/plugins/board/assets/img/@no.jpg" alt="">
                    <p>{{ xe_trans('xe::noPost') }}</p>
                </td>
            </tr>
            <!-- / NO ARTICLE -->
            @endif

            <!-- LIST -->
            @foreach($paginate as $item)
                <tr class="how-to-do-haveto-readed">
                    <td class="check"><input type="checkbox" title="{{xe_trans('xe::manage')}}" class="bd_manage_check" value="{{ $item->id }}"></td>
                    <td class="favorite mb_hidden"><a href="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="@if($item->favorite !== null) on @endif xe-favorite" title="{{xe_trans('board::favorite')}}" data-id="{{ $item->id }}"><i class="xi-star"></i><span class="bd_hidden">{{xe_trans('board::favorite')}}</span></a></td>

                    @foreach ($config->get('listColumns') as $columnName)
                        @if ($columnName == 'title')
                            @if ($config->get('category') == true)
                                <td class="category mb_hidden column-category">{!! $item->boardCategory !== null ? xe_trans($item->boardCategory->categoryItem->word) : '' !!}</td>
                            @endif
                            <td class="title column-{{$columnName}}">

                                @if ($config->get('category') == true && $item->boardCategory !== null)
                                    <span class="category">{!! $item->boardCategory->categoryItem->word !!}</span>
                                @endif
                                <a href="{{$urlHandler->getShow($item, Input::all())}}" id="{{$columnName}}_{{$item->id}}">{!! $item->title !!}</a>

                                @if($item->commentCount > 0)
                                    <span class="reply_num mb_hidden" title="Replies">{{ $item->commentCount }}</span>
                                @endif

                                @if($item->isNew($config->get('newTime')))
                                    <span class="bd_ico_new"><i class="xi-new"></i><span class="bd_hidden">new</span></span>
                                @endif

                                @if ($item->fileCount > 0)
                                    <span class="bd_ico_file" data-count="{{ $item->fileCount }}"><i class="xi-clip"></i><span class="bd_hidden">file</span></span>
                                @endif
                                <div class="more_info pc_hidden">
                                    <a href="#" class="mb_autohr" class="__xe_user" data-id="{{$item->getUserId()}}">{!! $item->writer !!}</a>
                                    <span class="mb_time"><i class="xi-time"></i> {!! sprintf('‘%s', str_replace('-', '.', substr($item->createdAt, 2, 8))) !!}</span>
                                    <span class="mb_readnum"><i class="xi-eye"></i> {{ $item->readCount }}</span>
                                    <a href="#" class="mb_reply_num"><i class="xi-comment"></i> {{ $item->commentCount }}</a>
                                </div>
                            </td>
                        @elseif ($columnName == 'writer')
                            <td class="author mb_hidden column-{{$columnName}}"><a href="#" class="__xe_user" data-id="{{$item->getUserId()}}">{!! $item->writer !!}</a></td>
                        @elseif ($columnName == 'readCount')
                            <td class="read_num mb_hidden">{{ $item->{$columnName} }}</td>
                        @elseif (in_array($columnName, ['createdAt', 'updatedAt', 'deletedAt']))
                            <td class="time mb_hidden column-{{$columnName}}" title="{{ $item->{$columnName} }}">{!! sprintf('‘%s', str_replace('-', '.', substr($item->{$columnName}, 2, 8))) !!}</td>
                        @elseif (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) != null)
                            @if ($columnName == 'category')
                                <td class="category mb_hidden column-{{$columnName}}">{!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}</td>
                            @else
                                <td class="mb_hidden column-{{$columnName}}">{!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}</td>
                            @endif
                        @else
                            <td class="mb_hidden column-{{$columnName}}">{!! $item->{$columnName} !!}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
            <!-- /LIST -->
            </tbody>
        </table>
    </div>

    <div class="board_footer">
        {!! $paginationPresenter->render() !!}
        {!! $paginationMobilePresenter->render() !!}
        <div class="bd_dimmed"></div>
    </div>
</div>
<!-- /BOARD -->
