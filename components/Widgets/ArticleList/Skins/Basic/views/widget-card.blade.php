{{ app('xe.frontend')->css([
    $_skin::asset('css/widget-basic.css')
])->load() }}

<section class="xe-widget xe-widget-type--article-list xe-widget-skin--basic-card section-widget-funnyweb-main-board section-widget-funnyweb-main-board--card">
    <div class="widget-funnyweb-main-board">
        <div class="xe-widget__header title-box">
            <!-- [D] 타이틀 -->
            <h2 class="title">{{ $title }}</h2>
            <!-- [D] 전체보기 링크 -->
            @if ($more === true)
                <a href="{{ $urlMore }}" class="xe-widget__more-link more-link">
                    <i class="xi-angle-right"></i>
                    <span class="blind">{{ xe_trans('more') }}</span>
                </a>
            @endif
        </div>

        <div class="xe-widget__body content-box">
            <!-- pc 에서 노출 될 개수 클래스 추가 class="list--item-two" ~ three, four, five 까지 해놓았음 -->
            <ul class="xe-widget__items list list--item-two">
                @foreach ($list as $idx => $item)
                    <li>
                        <a href="{{ $urlHandler->getShow($item, [], $item->boardConfig) }}" class="item-link">
                            <div class="item-image-box">
                                <!-- [D] 카드형 이미지 -->
                                <div class="item-image item-image--scale-animation" @if($item->board_thumbnail_path) style="background-image: url('{{ $item->board_thumbnail_path }}');" @endif></div>
                            </div>
                        </a>
                        <a href="{{ $urlHandler->getShow($item, [], $item->boardConfig) }}" class="item-link">
                            <!-- [D] 게시물 제목 -->
                            <strong class="item__title">{{ $item->title }}</strong>
                            <!-- [D] 게시물 날짜 -->
                            <span class="item__date">{{ $item->created_at->format('Y-m-d') }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
