<div class="board_read">
    <div class="read_header">
        @if($showCategoryItem)
            <span class="category">{{ xe_trans($showCategoryItem->word) }}</span>
        @endif
        <h1><a href="#">{!! $title !!}</a></h1>
        <div class="more_info">
            <a href="#" class="mb_autohr" data-id="" data-text="{{ $writer }}">{{ $writer }}</a>
            <span class="mb_time" title="{{$currentDate}}"><i class="xi-time"></i> <span data-xe-timeago="{{$currentDate}}">{{$currentDate}}</span></span>
            <span class="mb_readnum"><i class="xi-eye"></i> 0</span>
        </div>
    </div>
    <div class="read_body">
        <div class="xe_content">
            {!! compile($config->get('boardId'), $content, $format === Xpressengine\Plugins\Board\Models\Board::FORMAT_HTML) !!}
        </div>
    </div>
</div>