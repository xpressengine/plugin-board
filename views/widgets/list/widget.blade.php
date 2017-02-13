<div class="list-widget">
    <h3>{{xe_trans($menuItem->title)}}</h3>
    <a href="{{instanceRoute('index', [], $menuItem->id)}}" class="link-more-board xe-hidden-sm xe-hidden-xs">{{xe_trans('more')}}<i class="xi-angle-right"></i></a>
    <a href="#"></a>
    <div class="table-wrap">
        <table class="article-table type2">
            <caption class="xe-sr-only">{{xe_trans($menuItem->title)}}</caption>
            <colgroup class="xe-hidden-sm xe-hidden-xs">
                <col width="130">
                <col>
                <col width="96">
            </colgroup>
            <tbody>
            @foreach ($list as $item)
            <tr>
                <!-- 카테고리 링크를 제공하지 않는 경우 a를 span으로 교체 <td><span class="xe-badge xe-primary">세미나/이벤트</span></td> -->
                <td>
                    @if ($boardConfig->get('category') == true && $item->boardCategory !== null)
                    <a href="{{instanceRoute('index', [], $menuItem->id, ['categoryId' => $item->boardCategory->itemId])}}" class="xe-badge">{{xe_trans($item->boardCategory->categoryItem->word)}}</a>
                    @endif
                </td>
                <td class="title">
                    <a href="{{$urlHandler->getShow($item)}}">
                        <strong class="xe-ellipsis">{!! $item->title !!}</strong>
                        <p class="xe-ellipsis xe-hidden-sm xe-hidden-xs">{{$item->pureContent}} </p>
                    </a>
                </td>
                <td class="xe-hidden-sm xe-hidden-xs">
                    <em data-xe-timeago="{{$item->createdAt}}">{{$item->createdAt}}</em>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <a href="{{instanceRoute('index', [], $menuItem->id)}}" class="link-more-board xe-visible-sm xe-visible-xs">{{xe_trans('more')}}<i class="xi-angle-right"></i></a>
    </div>
</div>
