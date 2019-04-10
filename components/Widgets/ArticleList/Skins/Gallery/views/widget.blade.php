{{ XeFrontend::css('plugins/board/assets/css/widget.gallery.css')->load() }}
<div class="gallery-widget">
    <h3 class="article-table-title">{{$title}}</h3>
    <a href="{{$more}}" class="link-more-board xe-hidden-sm xe-hidden-xs">{{xe_trans('more')}}<i class="xi-angle-right"></i></a>
    <div class="article-thumnail clearfix">
        <ul>
            @foreach ($list as $item)
            <li>
                <a href="{{$urlHandler->getShow($item)}}">
                    <div class="xe-thumnail-box" style="background-image: url('{{ $item->board_thumbnail_path }}');">

                    </div>
                    <div class="xe-title-area">
                        <div class="xe-title-category">
                            <span class="xe-sr-only">{{xe_trans('category')}}</span>
                            @if ($item->boardConfig->get('category') == true && $item->boardCategory !== null)
                                {{xe_trans($item->boardCategory->categoryItem->word)}}
                            @endif
                        </div>
                        <div class="xe-title-text xe-ellipsis"><span class="xe-sr-only">{{xe_trans('title')}}</span>{!! $item->title !!}</div>
                        <div class="xe-upload-time xe-visible-lg"><span class="xe-sr-only">Uploaded at</span><em data-xe-timeago="{{$item->created_at}}">{{$item->created_at}}</em></div>
                    </div>
                </a>
            </li>
            @endforeach

        </ul>
    </div>
    <a href="{{$more}}" class="link-more-board xe-visible-sm xe-visible-xs">{{xe_trans('more')}}<i class="xi-angle-right"></i></a>
</div>
