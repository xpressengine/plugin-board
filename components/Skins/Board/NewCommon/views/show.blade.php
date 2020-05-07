{{ XeFrontend::css('plugins/board/assets/css/new-board-show.css')->load() }}

{!! xe_trans($config->get('topViewContent', '')) !!}

<div class="xe-list-board-body">
    @foreach ($skinConfig['formColumns'] as $columnName)
        @switch ($columnName)
            @case ('title')
                <div class="xe-list-board-body__title">
                    @if ($config->get('category') === true && $item->boardCategory !== null && array_get($skinConfig, 'visibleShowCategory', 'show') === 'show')
                        <div class="xe-list-board-body__title-category">{{ xe_trans($item->boardCategory->getWord()) }}</div>
                    @endif
                    <h3 class="xe-list-board-body__title-text">{!! $item->title !!}</h3>
                    <div class="xe-list-board-body__title-post-info">
                        <div class="xe-list-board-body--left-box">
                            <div class="xe-list-board-list__writer">
                                @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                    <a href="#" class="mb_author"
                                       data-toggle="xe-page-toggle-menu"
                                       data-url="{{ route('toggleMenuPage') }}"
                                       data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                        <span class="xe-list-board-list__user-image xe-hidden-mobile" style="background: url({{ $item->user->getProfileImage() }});"><span class="blind">유저 이미지</span></span>
                                        <span class="xe-list-board-list__display_name xe-list-board-list__mobile-style">{{ $item->writer }}</span>
                                    </a>
                                @else
                                    <a href="#">
                                        <span class="xe-list-board-list__user-image xe-hidden-mobile"><span class="blind">유저 이미지</span></span>
                                        <span class="xe-list-board-list__display_name xe-list-board-list__mobile-style">{{ $item->writer }}</span>
                                    </a>
                                @endif
                            </div>
                            
                            <div class="xe-list-board-list-item___detail-info">
                                <span class="xe-list-board-list-item___detail xe-list-board-list-item___detail-read_count xe-list-board-list__mobile-style"><span class="xe-list-board-list-item___detail-label">{{ xe_trans('board::read_count') }}</span> <span class="xe-list-board-list-item___detail-number">{{ number_format($item->read_count) }}</span></span>
                                <span class="xe-list-board-list-item___detail xe-list-board-list-item___detail-create_at xe-list-board-list__mobile-style"><span class="xe-list-board-list-item___detail-label">{{ xe_trans('board::created_at') }}</span> {{ $item->created_at->format('Y. m. d. H:i:s') }}</span>
                            </div>
                        </div>
                        <div class="xe-list-board-body--right-box">
                            <div class="xe-list-board-list__icon-box">
                                @if (array_get($skinConfig, 'visibleShowShare', 'show') === 'show')
                                    <span class="xe-list-board-list__icon xe-list-board-list__share">
                                        {!! uio('share', [
                                            'item' => $item,
                                            'url' => Request::url(),
                                        ]) !!}
                                    </span>
                                @endif

                                @if (Auth::check() === true && array_get($skinConfig, 'visibleShowFavorite', 'show') === 'show')
                                    <span class="xe-list-board-list__icon xe-list-board-list__bookmark">
                                        <a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="xe-list-board-body__link @if($item->favorite !== null) on @endif __xe-bd-favorite"><i class="xi-bookmark-o"></i></a>
                                    </span>
                                @endif
                                
                                <span class="xe-list-board-list__icon xe-list-board-list__more">
                                    <a href="#" class="xe-list-board-body__link" data-toggle="xe-page-toggle-menu" data-url="{{route('toggleMenuPage')}}" data-data='{!! json_encode(['id'=>$item->id,'type'=>'module/board@board','instanceId'=>$item->instance_id]) !!}' data-side="dropdown-menu-right"><i class="xi-ellipsis-h"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @break
            
            @case ('content')
                <div class="xe-list-board-body__article">
                    <div class="xe-list-board-body__article-text">
                        {!! compile($item->instance_id, $item->content, $item->format === Xpressengine\Plugins\Board\Models\Board::FORMAT_HTML) !!}
                    </div>

                    @if ($config->get('useTag') == true)
                        <div class="xe-list-board-body__article-tag">
                            <ul class="xe-list-board-body__article-tag-list">
                            @foreach ($item->tags->toArray() as $tag)
                                <li class="xe-list-board-body__article-tag-list-item"><a href="{{ $urlHandler->get('index', ['searchTag' => $tag['word']], $item->instanceId) }}" class="xe-list-board-body__link">{{ $tag['word'] }}</a></li>
                            @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                @break
            
            @default
                @if (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) !== null && isset($dynamicFieldsById[$columnName]) && $dynamicFieldsById[$columnName]->get('use') === true)
                    <div class="__xe_{{$columnName}} __xe_section">
                        {!! $fieldType->getSkin()->show($item->getAttributes()) !!}
                    </div>
                @endif
        
                @break
        @endswitch
    @endforeach

    <div class="xe-list-board-body__more-info">
        <div class="xe-list-board-body--left-box">
            @if ($config->get('assent') === true || $config->get('dissent') === true)
                <div class="xe-list-board-body--like-box-wrapper">
                    <div class="xe-list-board-body__like-box">
                        @if ($config->get('assent') === true)
                            <div class="xe-list-board-list__box-assent_count xe-list-board-body__like-box-item">
                                <span class="blind">{{ xe_trans('board::like') }}</span>
                                <a href="#" data-url="{{ $urlHandler->get('vote', ['option' => 'assent', 'id' => $item->id]) }}" class="bd_ico bd_like @if($handler->hasVote($item, Auth::user(), 'assent') === true) voted @endif">
                                    <img src="{{ url('plugins/board/assets/img/assent.svg') }}" alt="추천 아이콘">
                                </a>
                                <a href="#" data-url="{{ $urlHandler->get('votedUsers', ['option' => 'assent', 'id' => $item->id]) }}" class="bd_like_num" data-id="{{$item->id}}">
                                    <span class="xe-list-board-list__assent_count">{{ number_format($item->assent_count) }}</span>
                                </a>
                            </div>
                        @endif
                        
                        @if ($config->get('dissent') === true)
                            <div class="xe-list-board-list__box-dissent_count xe-list-board-body__like-box-item">
                                <span class="blind">{{ xe_trans('board::hate') }}</span>
                                <a href="#" data-url="{{ $urlHandler->get('vote', ['option' => 'dissent', 'id' => $item->id]) }}" class="bd_ico bd_like @if($handler->hasVote($item, Auth::user(), 'dissent') === true) voted @endif">
                                    <img src="{{ url('plugins/board/assets/img/dissent.svg') }}" alt="비추천 아이콘">
                                </a>
                                <a href="#" data-url="{{ $urlHandler->get('votedUsers', ['option' => 'dissent', 'id' => $item->id]) }}" class="bd_like_num bd_hate_num" data-id="{{$item->id}}">
                                    <span class="xe-list-board-list__dissent_count">{{ number_format($item->dissent_count) }}</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        <div class="xe-list-board-body--right-box">
            @if($isManager === true || $item->user_id === Auth::user()->getId() || $item->user_type === $item::USER_TYPE_GUEST)
                <div class="xe-list-board-body__edit-box">
                    <span class="xe-list-board-body__edit-item xe-list-board-body__edit"><a href="{{ $urlHandler->get('edit', array_merge(Request::all(), ['id' => $item->id])) }}" class="xe-list-board-body__link">{{ xe_trans('xe::update') }}</a></span>
                    <span class="xe-list-board-body__edit-item xe-list-board-body__delete"><a href="#" data-url="{{ $urlHandler->get('destroy', array_merge(Request::all(), ['id' => $item->id])) }}" class="xe-list-board-body__link bd_delete">{{ xe_trans('xe::delete') }}</a></span>
                </div>
            @endif
            
            <div class="xe-list-board-list__icon-box">
                @if (array_get($skinConfig, 'visibleShowShare', 'show') === 'show')
                    <span class="xe-list-board-list__icon xe-list-board-list__share">
                        {!! uio('share', [
                            'item' => $item,
                            'url' => Request::url(),
                        ]) !!}
                    </span>
                @endif
                
                @if (Auth::check() === true && array_get($skinConfig, 'visibleShowFavorite', 'show') === 'show')
                    <span class="xe-list-board-list__icon xe-list-board-list__bookmark">
                        <a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="xe-list-board-body__link @if($item->favorite !== null) on @endif __xe-bd-favorite"><i class="xi-bookmark-o"></i></a>
                    </span>
                @endif
                <span class="xe-list-board-list__icon xe-list-board-list__more">
                    <a href="#" class="xe-list-board-body__link" data-toggle="xe-page-toggle-menu" data-url="{{route('toggleMenuPage')}}" data-data='{!! json_encode(['id'=>$item->id,'type'=>'module/board@board','instanceId'=>$item->instance_id]) !!}' data-side="dropdown-menu-right"><i class="xi-ellipsis-h"></i></a>
                </span>
            </div>
        </div>
    </div>
        
    @if (array_get($skinConfig, 'visibleShowMoreBoardItems', 'show') === 'show')
{{--        TODO 기능 적용 필요--}}
{{--            <div class="xe-list-board-body__more-post">--}}
{{--                <h4 class="xe-list-board-body__more-post-title"><span class="xe-list-board-body__more-post-title-category">'카테고리명'</span>의 다른 글</h4>--}}
{{--                <ul class="xe-list-board-body__more-post-list">--}}
{{--                    <li class="xe-list-board-body__more-post-list-item">--}}
{{--                        <a href="#" class="xe-list-board-body__more-post-list-item-link">디자이너는 인터랙션 디자인의 전문가가되어 유용하고 의미있는 사용자 인터페이스를 만듭니다. 사용하기 쉽고 의미있는 인터페이스가인터페이스가인 디자이너는 인터랙션 디자인의 전문가가되어 유용하고 의미있는 사용자 인터페이스를 만듭니다. 사용하기 쉽고 의미있는 인터페이스가인터페이스가인 디자이너는 인터랙션 디자인의 전문가가되어 유용하고 의미있는 사용자 인터페이스를 만듭니다. 사용하기 쉽고 의미있는 인터페이스가인터페이스가인</a>--}}
{{--                        <span class="xe-list-board-body__more-post-list-item-date">2020-04-05</span>--}}
{{--                    </li>--}}
{{--                    <li class="xe-list-board-body__more-post-list-item">--}}
{{--                        <a href="#" class="xe-list-board-body__more-post-list-item-link">터널만 들어가면 먹통되던 내비, 이젠 LTE로 끊김없이 안내받는다</a>--}}
{{--                        <span class="xe-list-board-body__more-post-list-item-date">2020-04-05</span>--}}
{{--                    </li>--}}
{{--                    <li class="xe-list-board-body__more-post-list-item">--}}
{{--                        <a href="#" class="xe-list-board-body__more-post-list-item-link">디자이너는 인터랙션 디자인의 전문가가되어 유용하고 의미있는 사용자 인터페이스를 만듭니다.</a>--}}
{{--                        <span class="xe-list-board-body__more-post-list-item-date">2020-04-05</span>--}}
{{--                    </li>--}}
{{--                    <li class="xe-list-board-body__more-post-list-item">--}}
{{--                        <a href="#" class="xe-list-board-body__more-post-list-item-link">디자이너가 지켜봐야 할 시각적 트렌드</a>--}}
{{--                        <span class="xe-list-board-body__more-post-list-item-date">2020-04-05</span>--}}
{{--                    </li>--}}
{{--                </ul>--}}
{{--            </div>--}}
    @endif
</div>

@if ($config->get('comment') === true && $item->boardData->allow_comment === 1)
    <div class="__xe_comment board_comment">
        {!! uio('comment', ['target' => $item]) !!}
    </div>
@endif

<!--bottomViewContent-->
{!! xe_trans($config->get('bottomViewContent', '')) !!}

{{--@if (isset($withoutList) === false || $withoutList === false)--}}
{{--    @include($_skinPath.'/views/index')--}}
{{--@endif--}}
