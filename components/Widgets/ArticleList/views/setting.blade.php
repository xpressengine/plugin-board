<div class="form-group">
    <label>게시판</label>
    <select name="board_id" class="form-control" multiple onclick="$('[name=more]').val($(this).val())">
        @foreach ($boardList as $item)
            <option value="{{$item['value']}}" @if(in_array($item['value'], (array)array_get($args, 'board_id')) ) selected="selected" @endif >{{xe_trans($item['text'])}}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>더보기 링크 게시판</label>
    <select name="more" class="form-control">
        @foreach ($boardList as $item)
            <option value="{{$item['value']}}" @if(array_get($args, 'more') == $item['value']) selected="selected" @endif >{{xe_trans($item['text'])}}</option>
        @endforeach
    </select>
</div>
