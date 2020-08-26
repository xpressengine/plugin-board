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
                    <h3 class="xe-list-board-body__title-text">
                        @if($item->status === $item::STATUS_NOTICE)
                            <span class="xe-list-board-body__title-notice">공지</span>
                        @endif
                        @if ($item->display === $item::DISPLAY_SECRET) <i class="xi-lock"></i> @endif
                            @if ($item->data->title_head != '')<span class="title-head title-head-{{$item->data->title_head}}">[{{$item->data->title_head}}]</span>@endif{!! $item->title !!}
                    </h3>
                    <div class="xe-list-board-body__title-post-info">
                        <div class="xe-list-board-body--left-box">
                            <div class="xe-list-board-list__writer">
                                @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                    <a href="#" class="mb_author"
                                       data-toggle="xe-page-toggle-menu"
                                       data-url="{{ route('toggleMenuPage') }}"
                                       data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                        @if (array_get($skinConfig, 'visibleShowProfileImage', 'on') === 'on')
                                            <span class="xe-list-board-list__user-image" style="background: url({{ $item->user->getProfileImage() }}) 50% 50%; background-size: 48px;"><span class="blind">유저 이미지</span></span>
                                        @endif
                                        @if (array_get($skinConfig, 'visibleShowDisplayName', 'on') === 'on')
                                            <span class="xe-list-board-list__display_name">{{ $item->writer }}</span>
                                        @endif
                                    </a>
                                @else
                                    @if (array_get($skinConfig, 'visibleShowProfileImage', 'on') === 'on')
                                        <span class="xe-list-board-list__user-image"><span class="blind">유저 이미지</span></span>
                                    @endif
                                    @if (array_get($skinConfig, 'visibleShowDisplayName', 'on') === 'on')
                                        <span class="xe-list-board-list__display_name">{{ $item->writer }}</span>
                                    @endif
                                @endif
                            </div>
                            
                            <div class="xe-list-board-list-item___detail-info">
                                @if (array_get($skinConfig, 'visibleShowReadCount', 'on') === 'on')
                                    <span class="xe-list-board-list-item___detail xe-list-board-list-item___detail-read_count xe-list-board-list__mobile-style"><span class="xe-list-board-list-item___detail-label">{{ xe_trans('board::read_count') }}</span> <span class="xe-list-board-list-item___detail-number">{{ number_format($item->read_count) }}</span></span>
                                @endif
                                @if (array_get($skinConfig, 'visibleShowCreatedAt', 'on') === 'on')
                                    <span class="xe-list-board-list-item___detail xe-list-board-list-item___detail-create_at xe-list-board-list__mobile-style"><span class="xe-list-board-list-item___detail-label blind">{{ xe_trans('board::created_at') }}</span> {{ $item->created_at->format('Y. m. d. H:i:s') }}</span>
                                @endif
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
                                        <a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="xe-list-board-body__link @if($item->favorite !== null) on @endif __xe-bd-bookmark">
                                            <!-- <img src="{{ url('plugins/board/assets/img/bookmark.svg') }}"> -->
                                            <div class="bookmark"></div>
                                        </a>
                                    </span>
                                @endif
                                
                                <span class="xe-list-board-list__icon xe-list-board-list__more">
                                    <a href="#" class="xe-list-board-body__link" data-toggle="xe-page-toggle-menu" data-url="{{route('toggleMenuPage')}}" data-data='{!! json_encode(['id'=>$item->id,'type'=>'module/board@board','instanceId'=>$item->instance_id]) !!}' data-side="dropdown-menu-right"><img src="{{ url('plugins/board/assets/img/ellipsis-h.svg') }}"></a>
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

    @if (count($item->files) > 0)
        <div class="xe-list-board-body__file-attach">
            <div class="xe-list-board-body__file-attach-count">
                <a href="#" class="xe-list-board-body__file-attach-link">
                    <i class="xi-paperclip"></i>
                    <span class="blind">{{ xe_trans('board::fileAttachedList') }}</span>
                    <strong class="bd_file_num">{{ $item->data->file_count }}</strong>
                </a>
                <div class="xe-list-board-body__file-attach-name">
                    @foreach ($item->files as $file)
                        <a href="{{ route('editor.file.download', ['instanceId' => $item->instance_id, 'id' => $file->id])}}" class="xe-list-board-body__file-attach-link">
                            <i class="xi-download"></i>
                            <span class="xe-list-board-body__file-attach-file-name">{{ $file->clientname }}</span>
                            <span class="xe-list-board-body__file-attach-volume">({{ bytes($file->size) }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="xe-list-board-body__more-info">
        <div class="xe-list-board-body--left-box">
            @if ($config->get('assent') === true || $config->get('dissent') === true)
                <div class="xe-list-board-body--like-box-wrapper">
                    <div class="xe-list-board-body__like-box">
                        @if ($config->get('assent') === true)
                            <div class="xe-list-board-list__box-assent_count xe-list-board-body__like-box-item">
                                <span class="blind">{{ xe_trans('board::like') }}</span>
                                <a href="#" data-url="{{ $urlHandler->get('vote', ['option' => 'assent', 'id' => $item->id]) }}" class="bd_ico bd_like @if($handler->hasVote($item, Auth::user(), 'assent') === true) voted @endif">
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
                                </a>
                                <a href="#" data-url="{{ $urlHandler->get('votedUsers', ['option' => 'dissent', 'id' => $item->id]) }}" class="bd_like_num bd_hate_num" data-id="{{$item->id}}">
                                    <span class="xe-list-board-list__dissent_count">{{ number_format($item->dissent_count) }}</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            <div class="bd_like_more" id="bd_like_more{{$item->id}}" data-id="{{$item->id}}"></div>
            
        </div>

        <div class="xe-list-board-body--right-box">
                <div class="xe-list-board-body__edit-box">
                    <span class="xe-list-board-body__edit-item xe-list-board-body__list"><a href="{{ $urlHandler->get('index', array_merge(Request::all())) }}" class="xe-list-board-body__link"><span class="xe-list-board__btn-text">목록</span></a></span>
                    @if($isManager === true || $item->user_id === Auth::user()->getId() || $item->user_type === $item::USER_TYPE_GUEST)
                    <span class="xe-list-board-body__edit-item xe-list-board-body__edit"><a href="{{ $urlHandler->get('edit', array_merge(Request::all(), ['id' => $item->id])) }}" class="xe-list-board-body__link">{{ xe_trans('xe::update') }}</a></span>
                    <span class="xe-list-board-body__edit-item xe-list-board-body__delete"><a href="#" data-url="{{ $urlHandler->get('destroy', array_merge(Request::all(), ['id' => $item->id])) }}" class="xe-list-board-body__link bd_delete">{{ xe_trans('xe::delete') }}</a></span>
                    @endif
                </div>
            
            
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
                        <a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="xe-list-board-body__link @if($item->favorite !== null) on @endif __xe-bd-bookmark">
                            <div class="bookmark"></div>
                        </a>
                    </span>
                @endif
                <span class="xe-list-board-list__icon xe-list-board-list__more">
                    <a href="#" class="xe-list-board-body__link" data-toggle="xe-page-toggle-menu" data-url="{{route('toggleMenuPage')}}" data-data='{!! json_encode(['id'=>$item->id,'type'=>'module/board@board','instanceId'=>$item->instance_id]) !!}' data-side="dropdown-menu-right"><img src="{{ url('plugins/board/assets/img/ellipsis-h.svg') }}"></a>
                </span>
            </div>
        </div>
    </div>
        
    @if (array_get($skinConfig, 'visibleShowMoreBoardItems', 'show') === 'show')
        <div class="xe-list-board-body__more-post">
            <h4 class="xe-list-board-body__more-post-title"><span class="xe-list-board-body__more-post-board-name">{{ xe_trans(current_menu()['title']) }}</span>의 다른 글</h4>
            <ul class="xe-list-board-body__more-post-list">
                @foreach ($boardMoreItems as $boardMoreItem)
                    <li class="xe-list-board-body__more-post-list-item">
                        <a href="{{ $urlHandler->getShow($boardMoreItem) }}" class="xe-list-board-body__more-post-list-item-link">
                            <span class="xe-list-board-body__more-post-list-item-title">{!! $boardMoreItem->title !!}</span>
                            <span class="xe-list-board-body__more-post-list-item-date">{{ $boardMoreItem->created_at->format('Y-m-d') }}</span>
                        </a>
                    </li>
                @endforeach

                @if ($boardMoreItems->count() === 0)
                    <li class="xe-list-board-body__more-post-list-item">
                        <span>등록된 게시물이 없습니다.</span>
                    </li>
                @endif
            </ul>
        </div>
    @endif
</div>

@if ($config->get('comment') === true && $item->boardData->allow_comment === 1)
    <div class="__xe_comment board_comment">
        {!! uio('comment', ['target' => $item]) !!}
    </div>
@endif

<!--bottomViewContent-->
{!! xe_trans($config->get('bottomViewContent', '')) !!}

<script>
$(document).ready(function() {
    $(".xe-list-board-body__file-attach-count > .xe-list-board-body__file-attach-link").click(function(event){
        event.preventDefault();
        $(".xe-list-board-body__file-attach-name").toggleClass("open");
    });

    $('.__xe-bd-bookmark').on('click', function (event) {
        event.preventDefault()
        var id = $(this).data('id')
        var url = $(this).data('url')

        window.XE.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {id: id}
        }).done(function (json) {
            if (json.favorite === true) {
                $('.__xe-bd-bookmark').addClass('on')
            } else {
                $('.__xe-bd-bookmark').removeClass('on')
            }
        })
    })
});
</script>
