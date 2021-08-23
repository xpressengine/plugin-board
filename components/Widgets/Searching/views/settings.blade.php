<div class="form-group">
    <label>게시판 선택</label>
    <select name="board_id" class="form-control">
        @foreach ($boards as $board)
            <option value="{{ $board['id'] }}" @if(in_array($board['id'], (array)(\Illuminate\Support\Arr::get($args, 'board_id')))) selected="selected" @endif>{{ xe_trans($board['text']) }}</option>
        @endforeach
    </select>
</div>