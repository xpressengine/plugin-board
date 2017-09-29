{{ XeFrontend::js('/assets/vendor/jqueryui/jquery.event.drag-2.2.js')->appendTo('head')->load() }}
{{ XeFrontend::js('/assets/vendor/slickgrid/slick.core.js')->appendTo('head')->load() }}
{{ XeFrontend::js('/assets/vendor/slickgrid/slick.formatters.js')->appendTo('head')->load() }}
{{ XeFrontend::js('/assets/vendor/slickgrid/slick.grid.js')->appendTo('head')->load() }}
{{ XeFrontend::js('/assets/vendor/slickgrid/slick.dataview.js')->appendTo('head')->load() }}
{{ XeFrontend::css('/assets/vendor/slickgrid/slick.grid.css')->load() }}

    <div class="board_read">
        @foreach ($skinConfig['formColumns'] as $columnName)
            @if($columnName === 'title')
                <div class="read_header">
                    @if($item->status == $item::STATUS_NOTICE)
                        <span class="category">{{ xe_trans('xe::notice') }} @if($config->get('category') == true && $item->boardCategory !== null){{ xe_trans($item->boardCategory->getWord()) }}@endif</span>
                    @elseif($config->get('category') == true && $item->boardCategory !== null)
                        <span class="category">{{ xe_trans($item->boardCategory->getWord()) }}</span>
                    @endif
                    <h1><a href="{{ $urlHandler->getShow($item) }}">{!! $item->title !!}</a></h1>

                    <div class="more_info">
                        <!-- [D] 클릭시 클래스 on 적용 -->
                        @if ($item->hasAuthor() && $config->get('anonymity') === false)
                            <a href="{{ sprintf('/@%s', $item->getUserId()) }}" class="mb_autohr"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{ route('toggleMenuPage') }}"
                               data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>{{ $item->writer }}</a>
                        @else
                            <a class="mb_autohr">{{ $item->writer }}</a>
                        @endif

                        <span class="mb_time" title="{{$item->created_at}}"><i class="xi-time"></i> <span data-xe-timeago="{{$item->created_at}}">{{$item->created_at}}</span></span>
                        <span class="mb_readnum"><i class="xi-eye"></i> {{$item->read_count}}</span>
                    </div>
                </div>
            @elseif($columnName === 'content')
                <div class="read_body">
                    <div class="xe_content">
                        {!! compile($item->instance_id, $item->content, $item->format === Xpressengine\Plugins\Board\Models\Board::FORMAT_HTML) !!}
                    </div>
                </div>
            @elseif (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) != null)
                <div class="__xe_{{$columnName}} __xe_section">
                    {!! $fieldType->getSkin()->show($item->getAttributes()) !!}
                </div>
            @endif
        @endforeach

        <div class="read_footer">
            @if (count($item->files) > 0)
            <div class="bd_file_list">
                <!-- [D] 클릭시 클래스 on 적용 -->
                <a href="#" class="bd_btn_file"><i class="xi-paperclip"></i><span class="xe-sr-only">{{trans('board::fileAttachedList')}}</span> <strong class="bd_file_num">{{ $item->data->file_count }}</strong></a>
                <ul>
                    @foreach($item->files as $file)
                        <li><a href="{{ route('editor.file.download', ['instanceId' => $item->instance_id, 'id' => $file->id])}}"><i class="xi-download"></i> {{ $file->clientname }} <span class="file_size">({{ bytes($file->size) }})</span></a></li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="bd_function">
                <div class="bd_function_l">
                    <!-- [D] 클릭시 클래스 on 적용 및 bd_like_more 영역 diplay:block -->
                    <a href="#" data-url="{{ $urlHandler->get('vote', ['option' => 'assent', 'id' => $item->id]) }}" class="bd_ico bd_like @if($handler->hasVote($item, Auth::user(), 'assent') === true) voted @endif"><i class="xi-heart"></i><span class="xe-sr-only">{{ trans('board::like') }}</span></a>
                    <a href="#" data-url="{{ $urlHandler->get('votedUsers', ['option' => 'assent', 'id' => $item->id]) }}" class="bd_like_num" data-id="{{$item->id}}">{{$item->assent_count}}</a>

                    <a href="#" data-url="{{$urlHandler->get('favorite', ['id' => $item->id])}}" class="bd_ico bd_favorite @if($item->favorite !== null) on @endif __xe-bd-favorite"><i class="xi-star"></i><span class="xe-sr-only">{{ trans('board::favorite') }}</span></a>

                    {!! uio('share', [
                        'item' => $item,
                        'url' => Request::url(),
                    ]) !!}
                </div>

                <div class="bd_function_r">
                    @if($isManager == true || $item->user_id == Auth::user()->getId() || $item->user_type === $item::USER_TYPE_GUEST)
                        <a href="{{ $urlHandler->get('edit', array_merge(Request::all(), ['id' => $item->id])) }}" class="bd_ico bd_modify"><i class="xi-eraser"></i><span class="xe-sr-only">{{ xe_trans('xe::update') }}</span></a>
                        <a href="#" class="bd_ico bd_delete" data-url="{{ $urlHandler->get('destroy', array_merge(Request::all(), ['id' => $item->id])) }}"><i class="xi-trash"></i><span class="xe-sr-only">{{ xe_trans('xe::delete') }}</span></a>
                    @endif
                    <div class="bd_more_area">
                        <!-- [D] 클릭시 클래스 on 적용 -->
                        <a href="#" class="bd_ico bd_more_view" data-toggle="xe-page-toggle-menu" data-url="{{route('toggleMenuPage')}}" data-data='{!! json_encode(['id'=>$item->id,'type'=>'module/board@board','instanceId'=>$item->instance_id]) !!}' data-side="dropdown-menu-right"><i class="xi-ellipsis-h"></i><span class="xe-sr-only">{{ xe_trans('xe::more') }}</span></a>
                    </div>
                </div>
                <div class="bd_like_more" id="bd_like_more{{$item->id}}" data-id="{{$item->id}}"></div>
            </div>
        </div>
    </div>

    <style>
        .xe-toggle-menu {
            min-width: 140px;
            padding: 8px 0;
            border: 1px solid #bebebe;
            border-radius: 4px;
            background-color: #fff;
            list-style: none;
        }
        .xe-toggle-menu li {
            height: 30px;
        }
        .xe-toggle-menu li > a {
            overflow: hidden;
            display: block;
            height: 100%;
            padding: 0 16px;
            font-size: 14px;
            line-height: 30px;
            color: #2c2e37;
        }
        .xe-toggle-menu li > a:hover {
            background-color: #f4f4f4;
        }
    </style>
    <!-- 댓글 -->
    @if ($config->get('comment') === true && $item->boardData->allow_comment === 1)
    <div class="__xe_comment board_comment">
        {!! uio('comment', ['target' => $item]) !!}
    </div>
    @endif
    <!-- // 댓글 -->

@if (isset($withoutList) === false || $withoutList === false)
    <!-- 리스트 -->
    @include($_skinPath.'/views/index')
@endif


