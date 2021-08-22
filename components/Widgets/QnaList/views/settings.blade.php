<div class="form-group">
    <label>정렬</label>
    <select name="order_type" class="form-control">
        <option value="recently_created" @if(array_get($args, 'order_type') == 'recently_created') selected="selected" @endif >{{ xe_trans('board::recentlyCreated') }}</option>
        <option value="recently_updated" @if(array_get($args, 'order_type') == 'recently_updated') selected="selected" @endif >{{ xe_trans('board::recentlyUpdated') }}</option>
        <option value="assent_count" @if(array_get($args, 'order_type') == 'assent_count') selected="selected" @endif >{{xe_trans('board::assentOrder')}}</option>
        <option value="read_count" @if(array_get($args, 'order_type') == 'read_count') selected="selected" @endif >{{xe_trans('board::read_count')}}</option>
        <option value="random" @if(array_get($args, 'order_type') == 'random') selected="selected" @endif >{{xe_trans('board::random')}}</option>
    </select>
</div>

<div class="form-group">
    <label>글 수</label>
    <input type="number" placeholder="출력할 게시물의 수를 입력해주세요." name="take" class="form-control" value="{{ array_get($args, 'take', 5) }}" />
</div>

<div class="form-group">
    <label>최근 몇일</label>
    <input type="number" name="recent_date" class="form-control" value="{{ array_get($args, 'recent_date') }}" />
</div>

<div class="form-group">
    <label>채택 여부에 따른 필터링</label>
    <select name="adopt_filter" class="form-control">
        <option value="" @if(\Illuminate\Support\Arr::get($args, 'adopt_filter', '')) selected="selected" @endif>사용안함</option>
        <option value="only_adopted" @if(\Illuminate\Support\Arr::get($args, 'adopt_filter', '') === 'only_adopted') selected="selected" @endif>채택 완료된 게시글만 필터링</option>
        <option value="only_unAdopted" @if(\Illuminate\Support\Arr::get($args, 'adopt_filter', '') === 'only_unAdopted') selected="selected" @endif>답변 작성이 필요한 게시글만 필터링</option>
    </select>
</div>

<div class="form-group">
    <label>QnA 게시판 선택</label>
    <select name="board_id" class="form-control">
        @foreach ($boards as $board)
            <option value="{{ $board['id'] }}" @if(in_array($board['id'], (array)(\Illuminate\Support\Arr::get($args, 'board_id')))) selected="selected" @endif>{{ xe_trans($board['text']) }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>더보기</label>
    <select name="using_more" class="form-control">
        <option value="true" @if(\Illuminate\Support\Arr::get($args, 'using_more', 'true') === 'true') selected="selected" @endif>사용</option>
        <option value="false" @if(\Illuminate\Support\Arr::get($args, 'using_more', 'true') === 'false') selected="selected" @endif>사용안함</option>
    </select>
</div>

<div class="form-group">
    <label>더보기 명</label>
    <input type="text" placeholder="더보기에 사용할 문구를 입력해주세요." name="more_text" class="form-control" value="{{ array_get($args, 'more_text', '') }}" />
</div>
