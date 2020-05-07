<div class="xe-modal-header">
    <button type="button" class="btn-close" data-dismiss="xe-modal" aria-label="Close"><i class="xi-close"></i></button>
    <strong class="xe-modal-title">
        @if ($option === 'assent')
            {{ xe_trans('board::assentThisPosts', ['count' => $count]) }}
        @elseif ($option === 'dissent')
            {{ xe_trans('board::dissentThisPosts', ['count' => $count]) }}
        @endif
    </strong>
</div>

    <div class="xe-list-group" data-url="{{$urlHandler->get('votedUserList', ['option' => $option, 'id' => $item->id])}}"></div>

<div class="xe-modal-footer">
    <button type="button" class="xe-btn xe-btn-secondary" data-dismiss="xe-modal">{{xe_trans('xe::close')}}</button>
</div>
