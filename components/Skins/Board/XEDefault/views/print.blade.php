{{ XeFrontend::css('plugins/board/assets/css/new-board-show.css')->load() }}

<div class="xe-list-board-body">
    @foreach ($skinConfig['formColumns'] as $columnName)
        @switch ($columnName)
            @case ('title')
                <div class="xe-list-board-body__title">
                    @if ($config->get('category') === true && $item->boardCategory !== null && array_get($skinConfig, 'visibleShowCategory', 'show') === 'show')
                        <div class="xe-list-board-body__title-category">{{ xe_trans($item->boardCategory->getWord()) }}</div>
                    @endif
                    <h3 class="xe-list-board-body__title-text">
                        @if($item->status === $item::STATUS_NOTICE)
                            <span class="xe-list-board-body__title-notice">공지</span>
                        @endif
                        @if ($item->display === $item::DISPLAY_SECRET) <i class="xi-lock"></i> @endif
                        {!! $item->title !!}
                    </h3>
                    <div class="xe-list-board-body__title-post-info">
                        <div class="xe-list-board-body--left-box">
                            <div class="xe-list-board-list__writer">
                                @if ($item->hasAuthor() && $item->isNotAnonymity())
                                    @if (array_get($skinConfig, 'visibleShowProfileImage', 'on') === 'on')
                                        <span class="xe-list-board-list__user-image xe-hidden-mobile" style="background: url({{ $item->user->getProfileImage() }}); background-size: 28px;"><span class="blind">유저 이미지</span></span>
                                    @endif
                                    @if (array_get($skinConfig, 'visibleShowDisplayName', 'on') === 'on')
                                        <span class="xe-list-board-list__display_name xe-list-board-list__mobile-style">{{ $item->writer }}</span>
                                    @endif
                                @else
                                    @if (array_get($skinConfig, 'visibleShowProfileImage', 'on') === 'on')
                                        <span class="xe-list-board-list__user-image xe-hidden-mobile"><span class="blind">유저 이미지</span></span>
                                    @endif
                                    @if (array_get($skinConfig, 'visibleShowDisplayName', 'on') === 'on')
                                        <span class="xe-list-board-list__display_name xe-list-board-list__mobile-style">{{ $item->writer }}</span>
                                    @endif
                                @endif
                            </div>
    
                            <div class="xe-list-board-list-item___detail-info">
                                @if (array_get($skinConfig, 'visibleShowReadCount', 'on') === 'on')
                                    <span class="xe-list-board-list-item___detail xe-list-board-list-item___detail-read_count xe-list-board-list__mobile-style"><span class="xe-list-board-list-item___detail-label">{{ xe_trans('board::read_count') }}</span> <span class="xe-list-board-list-item___detail-number">{{ number_format($item->read_count) }}</span></span>
                                @endif
                                @if (array_get($skinConfig, 'visibleShowCreatedAt', 'on') === 'on')
                                    <span class="xe-list-board-list-item___detail xe-list-board-list-item___detail-create_at xe-list-board-list__mobile-style"><span class="xe-list-board-list-item___detail-label">{{ xe_trans('board::created_at') }}</span> {{ $item->created_at->format('Y. m. d. H:i:s') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @break

            @case ('content')
                <div class="xe-list-board-body__article">
                    <div class="xe-list-board-body__article-text">
                        {!! compile($item->instance_id, $item->content, $item->format === Xpressengine\Plugins\Board\Models\Board::FORMAT_HTML) !!}
                    </div>
                </div>
                @break

            @default
                @if (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) !== null && isset($dynamicFieldsById[$columnName]) && $dynamicFieldsById[$columnName]->get('use') === true)
                    <div class="__xe_{{$columnName}} __xe_section">
                        {!! $fieldType->getSkin()->show($item->getAttributes()) !!}
                    </div>
                @endif
                @break
        @endswitch
    @endforeach
</div>

<script>
    $(function() {
        window.print();
    });
</script>
