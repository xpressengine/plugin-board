<div class="board_read">
    <div class="read_header">
        @if(DynamicField::has($config->get('documentGroup'), 'category'))
        <span class="category">{!! DynamicField::get($config->get('documentGroup'), 'category')->getSkin()->show($item->getAttributes()) !!}</span>
        @endif
        <h1><a href="{{ $urlHandler->getShow($item) }}">{!! $item->title !!}</a></h1>
        <div class="more_info">
            <!-- [D] 클릭시 클래스 on 적용 -->
            @if ($item->userId != '')
                <a href="{{ sprintf('/@%s', $item->getAuthor()->getAuthIdentifier()) }}" class="mb_autohr __xe_member" data-id="{{$item->getUserId()}}" data-text="{{ $item->writer }}">{{ $item->writer }}</a>
            @else
                <a href="#" class="mb_autohr __xe_member" data-id="" data-text="{{ $item->writer }}">{{ $item->writer }}</a>
            @endif
            <span class="mb_time __xe_short_date" title="{{$item->createdAt}}" data-timestamp="{{strtotime($item->createdAt)}}"><i class="xi-time"></i> {{$item->createdAt}}</span>
            <span class="mb_readnum"><i class="xi-eye"></i> {{$item->readCount}}</span>
            <div class="ly_popup">
                <ul>
                    <li><a href="#">프로필 보기</a></li>
                    <li><a href="#">쪽지 보내기</a></li>
                    <li><a href="#">배포 자료 목록</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="read_body">
        <div class="xe_content">
            {!! uio('contentCompiler', ['content' => $item->content]) !!}
        </div>
    </div>

    <div class="read_footer">
        <div class="bd_file_list">
            <!-- [D] 클릭시 클래스 on 적용 -->
            <a href="#" class="bd_btn_file"><i class="xi-clip"></i><span class="bd_hidden">{{trans('board::fileAttachedList')}}</span> <strong class="bd_file_num">0</strong></a>
        </div>
        <div class="bd_function">
            <div class="bd_function_l">
                <!-- [D] 클릭시 클래스 on 적용 및 bd_like_more 영역 diplay:block -->
                <a href="#" class="bd_ico bd_like"><i class="xi-heart"></i><span class="bd_hidden">좋아요</span></a><a href="#" class="bd_like_num">{{$item->assentCount}}</a>
                <a href="#" class="bd_ico bd_favorite"><i class="xi-star"></i><span class="bd_hidden">즐겨찾기</span></a>
                <div class="bd_share_area">
                    <!-- [D] 클릭시 클래스 on 적용 -->
                    <a href="#" class="bd_ico bd_share"><i class="xi-external-link"></i><span class="bd_hidden">공유</span></a>
                    <div class="ly_popup">
                        <ul>
                            <li><a href="#"><i class="xi-facebook"></i> 페이스북</a></li>
                            <li><a href="#"><i class="xi-twitter"></i> 트위터</a></li>
                            <li><a href="#"><i class="xi-link"></i> 고유주소</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="bd_function_r">
                @if ($item->alterPerm(Auth::user()))
                    <a href="{{ $urlHandler->get('edit', array_merge(Input::all(), ['id' => $item->id])) }}" class="bd_ico bd_modify"><i class="xi-eraser"></i><span class="bd_hidden">{{ xe_trans('xe::update') }}</span></a>
                @endif
                @if ($item->deletePerm(Auth::user()))
                    <a href="{{ $urlHandler->get('destroy', array_merge(Input::all(), ['id' => $item->id])) }}" class="bd_ico bd_delete"><i class="xi-trash"></i><span class="bd_hidden">{{ xe_trans('xe::delete') }}</span></a>
                @endif
                <a href="{{ $urlHandler->get('create', array_merge(Input::all(), ['parentId' => $item->id])) }}" class="bd_ico bd_reply"><i class="xi-trash"></i><span class="bd_hidden">{{ xe_trans('xe::reply') }}</span></a>
                <div class="bd_more_area">
                    <!-- [D] 클릭시 클래스 on 적용 -->
                    <a href="#" class="bd_ico bd_more_view __xe_manage_menu_document" data-instance-id="{{ $item->instanceId }}" data-id="{{ $item->id }}"><i class="xi-ellipsis-h"></i><span class="bd_hidden">{{ xe_trans('xe::more') }}</span></a>
                    <div class="ly_popup">
                        <ul>
                            <li><a href="#">신고</a></li>
                            <li><a href="#">스패머관리</a></li>
                            <li><a href="#">휴지통</a></li>
                            <li><a href="#">등등</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="bd_like_more">
                <ul>
                </ul>
            </div>
        </div>
    </div>
</div>