<div class="form-group">
    <label>사용할 이름</label>
    <input type="text" class="form-control" name="title" placeholder="공백시 '게시판'으로 나타납니다." value="{{array_get($args, 'title', 5)}}">
</div>
<div class="form-group">
    <label>리스트 수</label>
    <input type="number" name="take" class="form-control" value="{{array_get($args, 'take', 5)}}" />
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
