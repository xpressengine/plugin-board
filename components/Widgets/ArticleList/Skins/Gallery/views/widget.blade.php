{{ XeFrontend::css('plugins/board/assets/css/widget.gallery.css')->load() }}
<div class="gallery-widget">
    <h3 class="article-table-title">{{xe_trans($menuItem->title)}}</h3>
    <a href="{{instanceRoute('index', [], $menuItem->id)}}" class="article-table-more xe-hidden-sm xe-hidden-x">{{xe_trans('more')}}<i class="xi-angle-right"></i></a>
    <div class="article-thumnail">
        <ul>
            @foreach ($list as $item)
            <li>
                <a href="{{$urlHandler->getShow($item)}}">
                    <div class="xe-thumnail-box">
                        <img src="<?php echo $item->boardThumbnailPath ? : 'http://placehold.it/300x200'?>" alt="">
                    </div>
                    <div class="xe-title-area">
                        <div class="xe-title-category">
                            <span class="xe-sr-only">{{xe_trans('category')}}</span>
                            @if ($boardConfig->get('category') == true && $item->boardCategory !== null)
                                {{xe_trans($item->boardCategory->categoryItem->word)}}
                            @endif
                        </div>
                        <div class="xe-title-text xe-ellipsis"><span class="xe-sr-only">{{xe_trans('title')}}</span>{!! $item->title !!}</div>
                        <div class="xe-upload-time xe-visible-lg"><span class="xe-sr-only">Uploaded at</span><em data-xe-timeago="{{$item->createdAt}}">{{$item->createdAt}}</em></div>
                    </div>
                </a>
            </li>
            @endforeach

        </ul>
    </div>
    <a href="{{instanceRoute('index', [], $menuItem->id)}}" class="link-more-board xe-visible-sm xe-visible-xs">{{xe_trans('more')}}<i class="xi-angle-right"></i></a>
</div>