


<div class="form-group">
    <label>정렬</label>
    <select name="order_type" class="form-control">
        <option value="recentlyCreated" @if(array_get($args, 'order_type') == 'recentlyCreated') selected="selected" @endif >{{xe_trans('board::recentlyCreated')}}</option>
        <option value="recentlyUpdated" @if(array_get($args, 'order_type') == 'recentlyUpdated') selected="selected" @endif >{{xe_trans('board::recentlyUpdated')}}</option>
        <option value="assent_count" @if(array_get($args, 'order_type') == 'assent_count') selected="selected" @endif >{{xe_trans('board::assentOrder')}}</option>
    </select>
</div>
<div class="form-group">
    <label>최근 몇일</label>
    <input type="number" name="recent_date" class="form-control" value="{{array_get($args, 'recent_date')}}" />
</div>

<div class="form=-group">
    <label>더보기 버튼 여부</label>
    <div class="xe-btn-toggle">
        <label>
            <span class="sr-only">toggle</span>
                <input name="more" type="checkbox" @if(array_get($args, 'more')) checked="checked" @endif />
            <span class="toggle"></span>
        </label>
    </div>
</div>

<p>글 설정</p>
<hr>


<div class="form-group">
    <label>카테고리 선택</label>
    <select name="board_id" class="form-control" multiple>
        @foreach ($boardList as $item)
            <option value="{{$item['value']}}" @if(in_array($item['value'], (array)array_get($args, 'board_id')) ) selected="selected" @endif >{{xe_trans($item['text'])}}</option>
            @foreach($item['categories'] as $category)
                @include('board::components.Widgets.ArticleList.views.category', ['category'=>$category, 'depth'=>1, 'boardId'=>(array)array_get($args, 'board_id')])
            @endforeach
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>글 수</label>
    <input type="number" name="take" class="form-control" value="{{array_get($args, 'take', 5)}}" />
</div>
<script>
    $(function(){
        $('[name="@title"]').prev().html('타이틀');
        $('select[name=board_id]').change(function(e){
            if($(this).val().length>1){
                $('[name=more]').prop('checked',false);
                $('[name=more]').prop('disabled',true);
            }else{
                $('[name=more]').prop('disabled',false);
            }
        }).trigger('change');
    })
</script>
