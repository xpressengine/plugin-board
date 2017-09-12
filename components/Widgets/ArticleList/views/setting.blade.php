<div class="form-group">
    <label>게시판</label>
    <select name="board_id" class="form-control">
        @foreach ($boardList as $item)
            <option value="{{$item['value']}}" @if(array_get($args, 'board_id') == $item['value']) selected="selected" @endif >{{xe_trans($item['text'])}}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>리스트 수</label>
    <input type="number" name="take" class="form-control" value="{{array_get($args, 'take')}}" />
</div>

<div class="form-group">
    <label>최근 몇일</label>
    <input type="number" name="recent_date" class="form-control" value="{{array_get($args, 'recent_date')}}" />
</div>

<div class="form-group">
    <label>정렬</label>
    <select name="order_type" class="form-control">
        <option value="recentlyCreated" @if(array_get($args, 'order_type') == 'recentlyCreated') selected="selected" @endif >{{xe_trans('board::recentlyCreated')}}</option>
        <option value="recentlyUpdated" @if(array_get($args, 'order_type') == 'recentlyUpdated') selected="selected" @endif >{{xe_trans('board::recentlyUpdated')}}</option>
        <option value="assent_count" @if(array_get($args, 'order_type') == 'assent_count') selected="selected" @endif >{{xe_trans('board::assentOrder')}}</option>
    </select>
</div>