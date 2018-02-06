    <div class="board_read">
        @foreach ($skinConfig['formColumns'] as $columnName)
            @if($columnName === 'title')
                <div class="read_header">
                    @if($item->status == $item::STATUS_NOTICE)
                        <span class="category">{{ xe_trans('xe::notice') }} @if($config->get('category') == true && $item->boardCategory !== null){{ xe_trans($item->boardCategory->getWord()) }}@endif</span>
                    @elseif($config->get('category') == true && $item->boardCategory !== null)
                        <span class="category">{{ xe_trans($item->boardCategory->getWord()) }}</span>
                    @endif
                    <h1><a href="{{ $urlHandler->getShow($item) }}">{!! $item->title !!}</a></h1>

                    <div class="more_info">
                        <!-- [D] 클릭시 클래스 on 적용 -->
                        @if ($item->hasAuthor() && $config->get('anonymity') === false)
                            <a href="{{ sprintf('/@%s', $item->getUserId()) }}" class="mb_autohr"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{ route('toggleMenuPage') }}"
                               data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>{{ $item->writer }}</a>
                        @else
                            <a class="mb_autohr">{{ $item->writer }}</a>
                        @endif

                        <span class="mb_time" title="{{$item->created_at}}"><i class="xi-time"></i> <span data-xe-timeago="{{$item->created_at}}">{{$item->created_at}}</span></span>
                        <span class="mb_readnum"><i class="xi-eye"></i> {{$item->read_count}}</span>
                    </div>
                </div>
            @elseif($columnName === 'content')
                <div class="read_body">
                    <div class="xe_content">
                        {!! compile($item->instance_id, $item->content, $item->format === Xpressengine\Plugins\Board\Models\Board::FORMAT_HTML) !!}
                    </div>
                </div>
            @elseif (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) != null && isset($dynamicFieldsById[$columnName]) && $dynamicFieldsById[$columnName]->get('use') == true)
                <div class="__xe_{{$columnName}} __xe_section">
                    {!! $fieldType->getSkin()->show($item->getAttributes()) !!}
                </div>
            @endif
        @endforeach

        @foreach ($fieldTypes as $dynamicFieldConfig)
            @if (in_array($dynamicFieldConfig->get('id'), $skinConfig['formColumns']) === false && ($fieldType = XeDynamicField::getByConfig($dynamicFieldConfig)) != null && $dynamicFieldConfig->get('use') == true)
            <div class="__xe_{{$columnName}} __xe_section">
                {!! $fieldType->getSkin()->show($item->getAttributes()) !!}
            </div>
            @endif
        @endforeach

    </div>

    <style>
        // @FIXME
        .xe-toggle-menu {
            min-width: 140px;
            padding: 8px 0;
            border: 1px solid #bebebe;
            border-radius: 4px;
            background-color: #fff;
            list-style: none;
        }
        .xe-toggle-menu li {
            height: 30px;
        }
        .xe-toggle-menu li > a {
            overflow: hidden;
            display: block;
            height: 100%;
            padding: 0 16px;
            font-size: 14px;
            line-height: 30px;
            color: #2c2e37;
        }
        .xe-toggle-menu li > a:hover {
            background-color: #f4f4f4;
        }
    </style>



<script>
    $(function() {
        window.print();
    });
</script>
