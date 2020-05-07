<ul>
    @foreach ($logs as $log)
    <li @if($log->user->getId() == Auth::user()->getId()) class="on" @endif>
        <a href="#"
        data-toggle="xe-page-toggle-menu"
        data-url="{{ route('toggleMenuPage') }}"
        data-data='{!! json_encode(['id'=>$log->user->getId(), 'type'=>'user']) !!}'><img src="{{$log->user->getProfileImage()}}" alt="{{$log->user->getDisplayName()}}" title="{{$log->user->getDisplayName()}}"></a>
    </li>
    @endforeach
</ul>

@if ($option === 'assent')
    @if ($item->assent_count > 10)
        <p class="bd_like_more_text">{!! xe_trans('board::assentThisPosts', [
            'count'=> sprintf('<a href="#" data-toggle="xe-page-modal" data-url="%s" data-params="{}" data-callback="AssentVirtualGrid.init">%s</a>', $urlHandler->get('votedModal', ['option' => $option, 'id' => $item->id]), $item->assent_count),
        ]) !!}</p>
    @endif
@elseif ($option === 'dissent')
    @if ($item->dissent_count > 10)
        <p class="bd_like_more_text">{!! xe_trans('board::dissentThisPosts', [
            'count'=> sprintf('<a href="#" data-toggle="xe-page-modal" data-url="%s" data-params="{}" data-callback="AssentVirtualGrid.init">%s</a>', $urlHandler->get('votedModal', ['option' => $option, 'id' => $item->id]), $item->dissent_count),
        ]) !!}</p>
    @endif
@endif
