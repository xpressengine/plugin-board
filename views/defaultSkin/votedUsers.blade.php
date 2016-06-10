<ul>
    @foreach ($logs as $log)
        <li @if($log->user->getId() == Auth::user()->getId()) class="on" @endif><img src="{{$log->user->getProfileImage()}}" alt="{{$log->user->getDisplayName()}}" title="{{$log->user->getDisplayName()}}"></li>
    @endforeach
</ul>
<!-- [D] 최대 10명까지 노출하고 나머지 사용자는 modal에서 처리 -->
@if ($item->assentCount > 10)
    <p class="bd_like_more_text">{!! xe_trans('board::assentThisPosts', [
        'count'=> sprintf('<a href="#" data-toggle="xe-page-modal" data-url="%s" data-params="{}" data-callback="AssentVirtualGrid.init">%s</a>', $urlHandler->get('votedModal', ['option' => $option, 'id' => $item->id]), $item->assentCount - 1),
    ]) !!}</p>
@endif