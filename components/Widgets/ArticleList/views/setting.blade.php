<div class="form-group">
    <label>정렬</label>
    <select name="order_type" class="form-control">
        <option value="recentlyCreated" @if(array_get($args, 'order_type') == 'recentlyCreated') selected="selected" @endif >{{ xe_trans('board::recentlyCreated') }}</option>
        <option value="recentlyUpdated" @if(array_get($args, 'order_type') == 'recentlyUpdated') selected="selected" @endif >{{ xe_trans('board::recentlyUpdated') }}</option>
        <option value="assent_count" @if(array_get($args, 'order_type') == 'assent_count') selected="selected" @endif >{{ xe_trans('board::assentOrder') }}</option>
        <option value="read_count" @if(array_get($args, 'order_type') == 'read_count') selected="selected" @endif >{{ xe_trans('board::readOrder') }}</option>
        <option value="random" @if(array_get($args, 'order_type') == 'random') selected="selected" @endif >{{ xe_trans('board::randomOrder') }}</option>
    </select>
</div>

<div class="form-group">
    <label>최근 몇일</label>
    <input type="number" name="recent_date" class="form-control" value="{{array_get($args, 'recent_date')}}" />
</div>

<div class="form-group">
    <label>공지 게시물 출력 타입</label>
    <select name="notice_type" class="form-control">
        <option value="onlyNotice" @if (array_get($args, 'notice_type') === 'onlyNotice') selected @endif>공지 게시물만 출력</option>
        <option value="withNotice" @if (in_array(array_get($args, 'noticeInList', array_get($args, 'notice_type', 'withOutNotice')), ['on', 'withNotice']) === true) selected @endif>공지 게시물을 포함해서 출력</option>
        <option value="withOutNotice" @if (in_array(array_get($args, 'noticeInList', array_get($args, 'notice_type', 'withOutNotice')), ['', 'withOutNotice']) === true) selected @endif>일반 게시물만 출력</option>
    </select>
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

<div class="form-group">
    <label>페이지네이션</label>
    <div class="xe-btn-toggle">
        <label>
            <span class="sr-only">toggle</span>
            <input name="pagination" type="checkbox" @if(array_get($args, 'pagination')) checked="checked" @endif />
            <span class="toggle"></span>
        </label>
    </div>
</div>

<div class="form-group">
    <label>페이지 이름</label>
    <input type="text" name="page_name" class="form-control" placeholder="page" value="{{array_get($args, 'pageName')}}" />
</div>

<div class="form=-group">
    <label>내가 쓴 글만 표시</label>
    <div class="xe-btn-toggle">
        <label>
            <span class="sr-only">toggle</span>
            <input name="display_my_posts" type="checkbox" @if(array_get($args, 'display_my_posts')) checked="checked" @endif />
            <span class="toggle"></span>
        </label>
    </div>
</div>

<div class="form=-group">
    <label>내가 찜한(favorite) 글만 표시</label>
    <div class="xe-btn-toggle">
        <label>
            <span class="sr-only">toggle</span>
            <input name="display_favorite_posts" type="checkbox" @if(array_get($args, 'display_favorite_posts')) checked="checked" @endif />
            <span class="toggle"></span>
        </label>
    </div>
</div>

<script>
    $(function(){
        $('[name="@title"]').prev().html('타이틀');
        $('select[name=board_id]').change(function(e){
            var $ids = $(this).val();
            var cnt = 0;

            $ids.forEach(function (val) {
                if (val.indexOf('category.') != 0) {
                    // 메뉴아이템이 있으면 카운트
                    cnt++;
                }
            })
            if(cnt>1){
                $('[name=more]').prop('checked',false);
                $('[name=more]').prop('disabled',true);
            }else{
                $('[name=more]').prop('disabled',false);
            }
        }).trigger('change');
    })
</script>
