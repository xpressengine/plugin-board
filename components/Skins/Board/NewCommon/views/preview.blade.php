<div class="board_read">
    @foreach ($skinConfig['formColumns'] as $columnName)
        @if($columnName === 'title')
            <div class="read_header">
                @if($showCategoryItem)
                    <span class="category">{{ xe_trans($showCategoryItem->word) }}</span>
                @endif

                <h1><a href="#">{!! $title !!}</a></h1>

                <div class="more_info">
                    <a href="#" class="mb_autohr" data-id="" data-text="{{ $writer }}">{{ $writer }}</a>
                    <span class="mb_time" title="{{$currentDate}}"><i class="xi-time"></i> <span data-xe-timeago="{{$currentDate}}">{{$currentDate}}</span></span>
                    <span class="mb_readnum"><i class="xi-eye"></i> 0</span>
                </div>

            </div>
        @elseif($columnName === 'content')
            <div class="read_body">
                {{-- @DEPRECATED .xe_content --}}
                <div class="xe-content xe_content">
                    {!! compile($config->get('boardId'), $content, $format === Xpressengine\Plugins\Board\Models\Board::FORMAT_HTML) !!}
                </div>
            </div>
        @elseif (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) != null && isset($dynamicFieldsById[$columnName]) && $dynamicFieldsById[$columnName]->get('use') == true)
            <div class="__xe_{{$columnName}} __xe_section">
                {!! $fieldType->getSkin()->show(request()->all()) !!}
            </div>
        @endif
    @endforeach

    @foreach ($fieldTypes as $dynamicFieldConfig)
        @if (in_array($dynamicFieldConfig->get('id'), $skinConfig['formColumns']) === false && ($fieldType = XeDynamicField::getByConfig($dynamicFieldConfig)) != null && $dynamicFieldConfig->get('use') == true)
            <div class="__xe_{{$columnName}} __xe_section">
                {!! $fieldType->getSkin()->show(request()->all()) !!}
            </div>
        @endif
    @endforeach
</div>
