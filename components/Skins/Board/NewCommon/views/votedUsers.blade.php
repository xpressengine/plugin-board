<ul class="bd_like_more_list">
    @foreach ($logs as $log)
    <li @if($log->user->getId() == Auth::user()->getId()) class="on" @endif>
        <a class="bd_like_more_list_item" href="#"
        data-toggle="xe-page-toggle-menu"
        data-url="{{ route('toggleMenuPage') }}"
        data-data='{!! json_encode(['id'=>$log->user->getId(), 'type'=>'user']) !!}'><img src="{{$log->user->getProfileImage()}}" alt="{{$log->user->getDisplayName()}}" title="{{$log->user->getDisplayName()}}"></a>
    </li>
    @endforeach

</ul>

<!-- [D] 최대 10명까지 노출하고 나머지 사용자는 modal에서 처리 -->
@if ($item->assent_count > 10)
<p class="bd_like_more_text">{!! xe_trans('board::assentThisPostsOthers', [
    'count'=> sprintf('<a href="#" class="bd_like_more_text_link" data-toggle="xe-page-modal" data-url="%s" data-params="{}" data-callback="AssentVirtualGrid.init">%s</a>', $urlHandler->get('votedModal', ['option' => $option, 'id' => $item->id]), $item->assent_count - 1),
]) !!}</p>
@endif