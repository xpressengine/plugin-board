@if($scriptInit === true)
    <script>
        // 여기에 스크립트를 넣으면 좋은데. 지금은 밖에서 처리하고 있음
    </script>
@endif

<input type="hidden" name="{{ $name }}" value="{{ $value }}" />
<a href="#" class="bd_select __xe_select_box_show">{{ $value ? xe_trans($text) : xe_trans($label) }}</a>
<div class="bd_select_list" data-name="{{ $name }}">
    <ul>
        <li><a href="#" data-value="">{{ xe_trans($label) }}</a></li>
        @foreach ($items as $key=>$item)
            <li><a href="#" data-value="{{$item['value']}}">{{xe_trans($item['text'])}}</a></li>
        @endforeach
    </ul>
</div>