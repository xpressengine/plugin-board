{{ XeFrontend::css('plugins/board/assets/css/xe-board-show.css')->load() }}

{!! xe_trans($config->get('topViewContent', '')) !!}

@foreach ($skinConfig['formColumns'] as $columnName)
    @switch ($columnName)
        @case ('title')
        <div class="xf-board-show-header">
            <div class="xf-post-contents xf-mb16">
                @if($item->status === $item::STATUS_NOTICE)
                    <div class="xf-post-notice xf-post-detail-title">
                        <span classs="xf-notice__text">{{ xe_trans('xe::notice') }}</span>
                    </div>
                @endif
                @if ($config->get('category') === true && $item->boardCategory !== null && array_get($skinConfig, 'visibleShowCategory', 'show') === 'show')
                    <div class="xf-post-category">
                        <span class="xf-post__text">{{ xe_trans($item->boardCategory->getWord()) }}</span>
                    </div>
                @endif
                <div class="xf-post-title xf-post-detail-title">
                    @if ($config->get('useTitleHead') === true && $item->data->title_head !== '')
                        <span class="xf-title-head xf-title-head-{{$item->data->title_head}}">
                            [{{$item->data->title_head}}]
                        </span>
                    @endif
                    <strong class="xf-post-title__text">
                        {{ $item->title }}
                    </strong>
                </div>
            </div>
            <div class="xf-post-info">
                {{--                    TODO    프로필 이미지 출력하는 옵션 사용하는 데 작성자가 탈퇴 했을 때 처리 방법 확인--}}
                @if (array_get($skinConfig, 'visibleShowProfileImage', 'on') === 'on')
                    <div class="xf-profile-img-box">
                        @if ($item->hasAuthor() && $config->get('anonymity') === false)
                            <a href="#" class="xf-a xf-item__writer-link"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{ route('toggleMenuPage') }}"
                               data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                <div class="xf-writer-profile-box xf-mr08">
                                    <div class="xf-writer-profile-img"
                                         style="background-image: url({{ $item->user->getProfileImage() }})"></div>
                                </div>
                            </a>
                        @endif
                    </div>
                @endif
                <div class="xf-detail-info">
                    @if (array_get($skinConfig, 'visibleShowDisplayName', 'on') === 'on')
                        @if ($item->hasAuthor() && $config->get('anonymity') === false)
                            <a href="#" class="xf-a xf-item__writer-link xf-mb06"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{ route('toggleMenuPage') }}"
                               data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                <span class="xf-writer__nickname">{{ $item->writer }}</span>
                            </a>
                        @else
                            <a href="#" class="xf-a xf-item__writer-link xf-mb06">
                                <span class="xf-writer__nickname">{{ $item->writer }}</span>
                            </a>
                        @endif
                    @endif
                    <ul class="xf-list xf-info-list">
                        @if (array_get($skinConfig, 'visibleShowCreatedAt', 'on') === 'on')
                            <li class="xf-info-item">
                                <span class="xf-info-item__text">{{ $item->created_at->format('Y.m.d') }}</span>
                            </li>
                        @endif
                        @if (array_get($skinConfig, 'visibleShowReadCount', 'on') === 'on')
                            <li class="xf-info-item">
                                <span class="xf-info-item__text xf-mr04">{{ xe_trans('board::read_count') }}</span>
                                <span class="xf-info-item__text">{{ number_format($item->read_count) }}</span>
                            </li>
                        @endif
                        <li class="xf-info-item">
                            <span
                                class="xf-info-item__text">{{ xe_trans('board::comment_count') }} {{ number_format($item->comment_count) }}</span>
                        </li>
                    </ul>
                </div>
                <div class="xf-function-icon-box">
                    <ul class="xf-function-icon-list xf-list">
                        @if (array_get($skinConfig, 'visibleShowShare', 'show') === 'show')
                            <li class="xf-function-item">
                                {!! uio('share', [
                                    'item' => $item,
                                    'url' => Request::url(),
                                ]) !!}
                            </li>
                        @endif
                        @if (Auth::check() === true && array_get($skinConfig, 'visibleShowFavorite', 'show') === 'show')
                            <li class="xf-function-item">
                                <a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}"
                                   class="xf-board-btn xf-bookmark xf-function-icon @if($item->favorite !== null) on @endif __xe-bd-bookmark">
                                </a>
                            </li>
                        @endif

                        <li class="xf-function-item">
                            <a href="#" class="xf-board-btn xf-toggle-menu xf-function-icon"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{route('toggleMenuPage')}}"
                               data-data='{!! json_encode(['id'=>$item->id,'type'=>'module/board@board','instanceId'=>$item->instance_id]) !!}'
                               data-side="dropdown-menu-right"></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @break

        @case ('content')
        <div class="xf-board-show-body">
            <div class="xf-post-article-contents">
                <div class="xf-post-article-text">
                    {!! compile($item->instance_id, $item->content, $item->format === Xpressengine\Plugins\Board\Models\Board::FORMAT_HTML) !!}
                </div>
                @if ($config->get('useTag') === true)
                    <div class="xf-post-article-tag-box">
                        <ul class="xf-list xf-post-tag-list">
                            @foreach ($item->tags->toArray() as $tag)
                                <li class="xf-post-tag-item">
                                    <a href="{{ $urlHandler->get('index', ['searchTag' => $tag['word']], $item->instanceId) }}"
                                       class="xf-post-tag__link xf-a">{{ $tag['word'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (count($item->files) > 0)
                    <div class="xf-board-attachment-box">
                        <div class="xf-attachment-box">
                            <a href="#" class="xf-attachment__link xf-a">
                                <div class="xf-file-icon"></div>
                                <span class="blind">{{ xe_trans('board::fileAttachedList') }}</span>
                                <strong class="xf-file-count-num bd_file_num">{{ $item->data->file_count }}</strong>
                            </a>
                            <div class="xf-attachment-name-box">
                                <ul class="xf-list xf-attachment-list">
                                    @foreach ($item->files as $file)
                                        <li class="xf-attachment-item">
                                            <a href="{{ route('editor.file.download', ['instanceId' => $item->instance_id, 'id' => $file->id])}}"
                                               class="xf-attachment-name__link xf-a">
                                                <i class="xi-download"></i>
                                                <span class="xf-attachment-name">{{ $file->clientname }}</span>
                                                <span class="xf-attachment-volume">({{ bytes($file->size) }})</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="xf-board-btn-box">
                @if ($config->get('assent') === true || $config->get('dissent') === true)
                    <div class="xf-show-assent-box">
                        <ul class="xf-assent-list xf-list">
                            @if ($config->get('assent') === true)
                                <li class="xf-assent-item">
                                    <div class="xf-assent-item-inner">
                                        <a href="#"
                                           data-url="{{ $urlHandler->get('vote', ['option' => 'assent', 'id' => $item->id]) }}"
                                           class="xf-assent__link xf-a bd_ico bd_like @if($handler->hasVote($item, Auth::user(), 'assent') === true) voted @endif">
                                            <div class="xf-assent xf-assent-icon"></div>
                                        </a>
                                        <a href="#"
                                           data-url="{{ $urlHandler->get('votedUsers', ['option' => 'assent', 'id' => $item->id]) }}"
                                           class="xf-assent__link xf-a bd_like_num" data-id="{{$item->id}}">
                                            <div class="xf-assent-text">{{ number_format($item->assent_count) }}</div>
                                        </a>
                                    </div>
                                </li>
                            @endif

                            @if ($config->get('dissent') === true)
                                <li class="xf-assent-item">
                                    <div class="xf-assent-item-inner">
                                        <a href="#"
                                           data-url="{{ $urlHandler->get('vote', ['option' => 'dissent', 'id' => $item->id]) }}"
                                           class="xf-assent__link xf-a bd_ico bd_like @if($handler->hasVote($item, Auth::user(), 'dissent') === true) voted @endif">
                                            <div class="xf-dissent xf-assent-icon"></div>
                                        </a>

                                        <a href="#"
                                           data-url="{{ $urlHandler->get('votedUsers', ['option' => 'dissent', 'id' => $item->id]) }}"
                                           class="xf-assent__link xf-a bd_like_num bd_hate_num" data-id="{{$item->id}}">
                                            <div class="xf-assent-text">{{ number_format($item->dissent_count) }}</div>
                                        </a>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
                <div class="xf-post-edit-box">
                    <ul class="xf-list xf-edit-list">
                        <li class="xf-edit-item">
                            <a href="{{ $urlHandler->get('index', array_merge(Request::all())) }}"
                               class="xf-edit__link xf-a">목록</a>
                        </li>
                        @if($isManager === true || $item->user_id === Auth::user()->getId() || $item->user_type === $item::USER_TYPE_GUEST)
                            <li class="xf-edit-item">
                                <a href="{{ $urlHandler->get('edit', array_merge(Request::all(), ['id' => $item->id])) }}"
                                   class="xf-edit__link xf-a">수정</a>
                            </li>
                            <li class="xf-edit-item">
                                <a href="#"
                                   data-url="{{ $urlHandler->get('destroy', array_merge(Request::all(), ['id' => $item->id])) }}"
                                   class="xf-edit__link xf-a bd_delete">삭제</a>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="xf-function-icon-box">
                    <ul class="xf-function-icon-list xf-list">
                        <li class="xf-function-item">
                            @if (array_get($skinConfig, 'visibleShowShare', 'show') === 'show')
                                {!! uio('share', [
                                    'item' => $item,
                                    'url' => Request::url(),
                                ]) !!}
                        </li>
                        @endif

                        @if (Auth::check() === true && array_get($skinConfig, 'visibleShowFavorite', 'show') === 'show')
                            <li class="xf-function-item">
                                <a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}"
                                   class="xe-list-board-body__link xf-board-btn xf-bookmark xf-function-icon @if($item->favorite !== null) on @endif __xe-bd-bookmark">
                                </a>
                            </li>
                        @endif

                        <li class="xf-function-item">
                            <a href="#" class="xe-list-board-body__link xf-board-btn xf-toggle-menu xf-function-icon"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{route('toggleMenuPage')}}"
                               data-data='{!! json_encode(['id'=>$item->id,'type'=>'module/board@board','instanceId'=>$item->instance_id]) !!}'
                               data-side="dropdown-menu-right"></a>
                        </li>
                    </ul>
                </div>
            </div>
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


@if (array_get($skinConfig, 'visibleShowMoreBoardItems', 'show') === 'show')
    <div class="xf-board-show-footer">
        <div class="xf-other-posts-list-box">
            <div class="xf-other-post-title-box xf-mb15">
                <strong class="xf-other-post-title">
                    <span class="xf-other-post-category">{{ xe_trans(current_menu()['title']) }}</span>
                    <span class="xf-other-post-text">의 다른글 보기</span>
                </strong>
            </div>
            <ul class="xf-list xf-other-post-list">
                @foreach ($boardMoreItems as $index => $boardMoreItem)
                    <li class="xf-other-post-item @if ($index > 3) xf-pc-display-fl @endif">
                        <a href="{{ $urlHandler->getShow($boardMoreItem) }}"
                           class="xf-other-post__link xf-a xf-ellipsis1">
                            <span class="xf-other-post__text">{!! $boardMoreItem->title !!}</span>
                        </a>
                    </li>
                @endforeach

                @if ($boardMoreItems->count() === 0)
                    <li class="xf-other-post-item">
                        <span>등록된 게시물이 없습니다.</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
@endif

@if ($config->get('comment') === true && $item->boardData->allow_comment === 1)
    <div class="__xe_comment board_comment">
        {!! uio('comment', ['target' => $item]) !!}
    </div>
@endif

<!--bottomViewContent-->
{!! xe_trans($config->get('bottomViewContent', '')) !!}

<script>
    $(document).ready(function () {
        $(".xf-board-attachment-box .xf-attachment__link").click(function (event) {
            event.preventDefault();
            $(".xf-attachment-name-box").toggleClass("open");
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
