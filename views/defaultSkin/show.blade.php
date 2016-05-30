{{ XeFrontend::js('/assets/core/common/js/toggleMenu.js')->appendTo('head')->load() }}

@if($visible == true)
    <div class="board_read">
        <div class="read_header">
            @if($item->status == $item::STATUS_NOTICE)
                <span class="category">{{ xe_trans('xe::notice') }} @if($config->get('category') == true && $showCategoryItem){{ $showCategoryItem ? xe_trans($showCategoryItem->word) : '' }}@endif</span>
            @elseif($config->get('category') == true && $showCategoryItem)
                <span class="category">{{ $showCategoryItem ? xe_trans($showCategoryItem->word) : '' }}</span>
            @endif
            <h1><a href="{{ $urlHandler->getShow($item) }}">{!! $item->title !!}</a></h1>

            <div class="more_info">
                <!-- [D] 클릭시 클래스 on 적용 -->
                @if ($item->userId != '' && $config->get('anonymity') === false)
                    <a href="{{ sprintf('/@%s', $item->user->getAuthIdentifier()) }}" class="mb_autohr" data-toggle="xeUserMenu" data-user-id="{{$item->getUserId()}}">{{ $item->writer }}</a>
                @else
                    <a class="mb_autohr">{{ $item->writer }}</a>
                @endif

                <span class="mb_time" title="{{$item->createdAt}}"><i class="xi-time"></i> <span data-xe-timeago="{{$item->createdAt}}">{{$item->createdAt}}</span></span>
                <span class="mb_readnum"><i class="xi-eye"></i> {{$item->readCount}}</span>
            </div>
        </div>
        <div class="read_body">
            <div class="xe_content">
                {!! uio('contentCompiler', ['content' => $item->content]) !!}
            </div>
        </div>

        <div class="dynamic_fields">
            @foreach ($configHandler->formColumns($instanceId) as $columnName)
                @if (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) != null)
                    <div class="__xe_{{$columnName}} __xe_section">
                        {!! $fieldType->getSkin()->show($item->getAttributes()) !!}
                    </div>
                @endif
            @endforeach
        </div>

        <div class="read_footer">
            <div class="bd_file_list">
                <!-- [D] 클릭시 클래스 on 적용 -->
                <a href="#" class="bd_btn_file"><i class="xi-clip"></i><span class="xe-sr-only">{{trans('board::fileAttachedList')}}</span> <strong class="bd_file_num">{{ $item->fileCount }}</strong></a>
                <ul>
                    @foreach($item->files() as $file)
                        <li><a href="#"><i class="xi-download"></i> {{ $file->clientname }} <span class="file_size">({{ bytes($file->size) }})</span></a></li>
                    @endforeach
                </ul>
            </div>
            <div class="bd_function">
                <div class="bd_function_l">
                    <!-- [D] 클릭시 클래스 on 적용 및 bd_like_more 영역 diplay:block -->
                    <a href="{{ $urlHandler->get('vote', ['option' => 'assent', 'id' => $item->id]) }}" class="bd_ico bd_like @if($handler->hasVote($item, Auth::user(), 'assent') === true) voted @endif"><i class="xi-heart"></i><span class="xe-sr-only">{{ trans('board::like') }}</span></a>
                    <a href="{{ $urlHandler->get('votedUsers', ['option' => 'assent', 'id' => $item->id]) }}" class="bd_like_num">{{$item->assentCount}}</a>

                    <a href="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="bd_ico bd_favorite @if($item->favorite !== null) on @endif __xe-bd-favorite"><i class="xi-star"></i><span class="xe-sr-only">{{ trans('board::favorite') }}</span></a>

                    <div class="bd_share_area">
                        <!-- [D] 클릭시 클래스 on 적용 -->
                        <a href="#" class="bd_ico bd_share"><i class="xi-external-link"></i><span class="xe-sr-only">{{ trans('board::share') }}</span></a>
                        <div class="ly_popup">
                            <ul>
                                <li><a href="http://www.facebook.com/sharer/sharer.php?u={{urlencode(Request::url())}}" target="_blank"><i class="xi-facebook"></i> {{ trans('board::facebook') }}</a></li>
                                <li><a href="https://twitter.com/intent/tweet?url={{urlencode(Request::url())}}" target="_blank"><i class="xi-twitter"></i> {{ trans('board::twitter') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bd_function_r">
                    @if($isManager == true || $item->userId == Auth::user()->getId() || $item->userType === $item::USER_TYPE_GUEST)
                        <a href="{{ $urlHandler->get('edit', array_merge(Input::all(), ['id' => $item->id])) }}" class="bd_ico bd_modify"><i class="xi-eraser"></i><span class="xe-sr-only">{{ xe_trans('xe::update') }}</span></a>
                    @endif
                    @if($isManager == true || $item->userId == Auth::user()->getId() || $item->userType === $item::USER_TYPE_GUEST)
                        <a href="{{ $urlHandler->get('destroy', array_merge(Input::all(), ['id' => $item->id])) }}" class="bd_ico bd_delete"><i class="xi-trash"></i><span class="xe-sr-only">{{ xe_trans('xe::delete') }}</span></a>
                    @endif
                    <a href="{{ $urlHandler->get('create', array_merge(Input::all(), ['parentId' => $item->id])) }}" class="bd_ico bd_reply"><i class="xi-reply"></i><span class="xe-sr-only">{{ xe_trans('xe::reply') }}</span></a>
                    <div class="bd_more_area">
                        <!-- [D] 클릭시 클래스 on 적용 -->
                        <a href="#" class="bd_ico bd_more_view __xe_manage_menu_document" data-instance-id="{{ $item->instanceId }}" data-id="{{ $item->id }}"><i class="xi-ellipsis-h"></i><span class="xe-sr-only">{{ xe_trans('xe::more') }}</span></a>
                        <div class="ly_popup">
                            <ul>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="bd_like_more">
                    <ul>
                    @foreach ($item->assents as $counterLog)
                        <li @if($counterLog->userId == Auth::user()->getId()) class="on" @endif><img src="{{$counterLog->user->getProfileImage()}}" alt="{{$counterLog->user->getDisplayName()}}" title="$counterLog->user->getDisplayName()"></li>
                    @endforeach
                    </ul>
                    <!-- [D] 최대 10명까지 노출하고 나머지 사용자는 modal에서 처리 -->
                    @if ($item->assentCount > 10)
                        <p class="bd_like_more_text">{!! xe_trans('board::assentThisPosts', [
                        'count'=> sprintf('<a href="#" data-toggle="xe-modal" data-target="#Modal2">%s</a>', $item->assentCount - 1),
                        ]) !!}}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 댓글 -->
    @if ($config->get('comment') === true && $item->boardData->allowComment === 1)
    <div class="__xe_comment">
        {!! uio('comment', ['target' => $item]) !!}
    </div>
    @endif
    <!-- // 댓글 -->
@else
    <script>
        XE.toast('alert', '{{xe_trans('board::notFoundDocument')}}');
    </script>
@endif
{{--end if visible == true --}}

<!-- 리스트 -->
@include($skinAlias.'.index')

