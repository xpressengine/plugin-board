<hr>

<div class="form-group">
    <label>게시판을 선택해주세요.</label>
    <select name="board_id" class="form-control">
        @foreach ($boards as $board)
            <option value="{{ $board['id'] }}" @if(in_array($board['id'], (array)(array_get($args, 'board_id')))) selected="selected" @endif>{{ xe_trans($board['text']) }}</option>
        @endforeach
    </select>
</div>

<hr>