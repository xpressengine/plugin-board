{{ XeFrontend::css('plugins/board/assets/css/xe-board-default.css')->load() }}

<form method="get" action="{{ $urlHandler->get('index') }}" class="__xe_search">
    <div class="xf-board-form-box">
        <div class="xf-board-search-box">
            <div class="xf-search-input-box">
                <input class="xf-search-input" type="text" placeholder="검색어를 입력하세요" name="title_pure_content" value="{{ Request::get('title_pure_content') }}">
            </div>
            <div class="xf-search-btn-box">
                <button class="xf-search-btn xf-board-btn" type="submit"></button>
            </div>
        </div>
        <div class="xe-board-dropdown-box">
            @if ($config->get('category') === true)
                <div class="xe-board-dropdown">
                    {!! uio('uiobject/board@new_select', [
                        'name' => 'category_item_id',
                        'label' => xe_trans('xe::category'),
                        'value' => Request::get('category_item_id'),
                        'items' => $categories
                    ]) !!}
                </div>
            @endif
            <div class="xe-board-dropdown">
                {!! uio('uiobject/board@new_select', [
                    'name' => 'order_type',
                    'label' => xe_trans('xe::order'),
                    'value' => Request::get('order_type', $config->get('orderType')),
                    'items' => $handler->getOrders()
                ]) !!}
            </div>
        </div>
    </div>
</form>

<ul class="xf-board-list base-board-borderTop xf-list">
    <!-- 리스트 헤더 -->
    <li class="xf-board-item xf-board-header-item">
        @foreach ($skinConfig['skinListColumns'] as $columnName)
            @if (in_array($columnName, $config->get('listColumns', [])) === false)
                @continue;
            @endif

            @if ($columnName === 'favorite')
                @if (Auth::check() === true)
                    <div class="xf-board-item-inner xf-item__bookmark">
                        <span class="xf-item-inner__text blind">북마크</span>
                        @if (Request::has('favorite'))
                            <a href="{{ $urlHandler->get('index', Request::except(['favorite', 'page'])) }}"
                               class="xf-item__bookmark-icon on">
                                <div class="bookmark"></div>
                            </a>
                        @else
                            <a href="{{ $urlHandler->get('index', array_merge(Request::except('page'), ['favorite' => 1])) }}"
                               class="xf-item__bookmark-icon">
                                <div class="bookmark"></div>
                            </a>
                        @endif
                    </div>
                @endif
            @elseif ($columnName === 'title')
                @if ($config->get('category') === true)
                    <div class="xf-board-item-inner xf-item__category">{{ xe_trans('board::category') }}</div>
                @endif
                <div class="xf-board-item-inner xf-item__title">{{ xe_trans('board::title') }}</div>
            @else
                @if (isset($dynamicFieldsById[$columnName]) === true)
                    <div
                        class="xf-board-item-inner xf-item__dynamic_field xf-item__dynamic_field-{{ $columnName }}">{{ xe_trans($dynamicFieldsById[$columnName]->get('label')) }}</div>
                @else
                    <div
                        class="xf-board-item-inner xf-item__{{ $columnName }}">{{ xe_trans('board::' . $columnName) }}</div>
                @endif
            @endif
        @endforeach
    </li>
    <!-- 공지사항 -->
    @foreach ($notices as $item)
        <li class="xf-board-item xf-board-notice">
            @foreach ($skinConfig['skinListColumns'] as $columnName)
                @if (in_array($columnName, $config->get('listColumns', [])) === false)
                    @continue;
                @endif

                @switch ($columnName)
                    @case ('favorite')
                    @if (Auth::check() === true)
                        <div class="xf-board-item-inner xf-item__bookmark">
                            <span class="xf-item-inner__text blind">북마크</span>
                            <a href="#" data-url="{{ $urlHandler->get('favorite', ['id' => $item->id]) }}"
                               class="xf-item__bookmark-icon @if ($item->favorite !== null) on @endif __xe-bd-favorite"
                               title="{{xe_trans('board::favorite')}}">
                                <div class="bookmark"></div>
                            </a>
                        </div>
                    @endif
                    @break

                    @case ('title')
                    @if ($config->get('category') === true)
                        <div class="xf-board-item-inner xf-item__category">
                                    <span class="xf-item-inner__text">
                                        {!! $item->boardCategory !== null ? xe_trans($item->boardCategory->categoryItem->word) : '' !!}
                                    </span>
                        </div>
                    @endif
                    <div class="xf-board-item-inner xf-item__title">
                        <a href="{{ $urlHandler->getShow($item, Request::all()) }}" class="xf-board-item__link xf-a">
                            <span class="xf-item-inner__label xf-notice-label">공지</span>
                            @if ($item->display === $item::DISPLAY_SECRET)
                                <div class="xf-secret-icon"></div>
                            @endif
                            <span class="xf-item-inner__text base-w500">
                                @if ($item->data->title_head !== null)
                                    <span class="xf-title-head xf-title-head-{{$item->data->title_head}}">[{{$item->data->title_head}}]</span>
                                @endif
                                <span class="xf-item__title-text">{{ $item->title }}</span>
                            </span>
                            <div class="xf-item-icon-box">
                                @if($item->comment_count > 0)
                                    <span
                                        class="xf-comment_count xf-ml03">[{{ number_format($item->comment_count) }}]</span>
                                @endif
                                <ul class="xf-item-icon-list xf-list">
                                    @if ($item->data->file_count > 0)
                                        <li class="xf-item-icon xf-attached-file xf-ml03"></li>
                                    @endif
                                    @if ($item->isNew($config->get('newTime')) && array_get($skinConfig, 'visibleIndexNewIcon', 'show') === 'show')
                                        <li class="xf-item-icon xf-new xf-ml03"></li>
                                    @endif
                                </ul>
                            </div>
                        </a>
                    </div>
                    @break
                    @case ('writer')
                    <div class="xf-board-item-inner xf-item__writer xf-item-detail">
                        @if ($item->hasAuthor() && $config->get('anonymity') === false)
                            <a href="#" class="mb_author xf-a xf-item__writer-link"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{ route('toggleMenuPage') }}"
                               data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                @if (array_get($skinConfig, 'visibleIndexDefaultProfileImage', 'on') === 'on')
                                    <div class="xf-writer-profile-box xf-pc-display-bl xf-mr08">
                                        <div class="xf-writer-profile-img"
                                             style="background-image: url({{ $item->user->getProfileImage() }});">
                                            <span
                                                class="blind">유저 이미지</span>
                                        </div>
                                    </div>
                                @endif
                                <span
                                    class="xf-item-inner__text">{{ $item->writer }}</span>
                            </a>
                        @else
                            @if (array_get($skinConfig, 'visibleIndexDefaultProfileImage', 'on') === 'on')
                                <div class="xf-writer-profile-box xf-pc-display-bl xf-mr08">
                                    <div class="xf-writer-profile-img"
                                         style="background-image: url({{ $item->user->getProfileImage() }});">
                                            <span
                                                class="blind">유저 이미지</span>
                                    </div>
                                </div>
                            @endif
                            <span
                                class="xf-item-inner__text">{{ $item->writer }}</span>
                        @endif
                    </div>
                    @break

                    @case ('created_at')
                    @case ('updated_at')
                    @case ('published_at')
                    <div class="xf-board-item-inner xf-item__{{ $columnName }} xf-item-detail">
                        <span
                            class="xf-item-inner__text xf-mo-display-in">{{ xe_trans('board::' . $columnName) }}</span>
                        <span
                            class="blind">{{ xe_trans('board::' . $columnName) }}</span>
                        <span class="xf-item-inner__text">{{ $item->{$columnName}->format('Y. m. d.') }}</span>
                    </div>
                    @break

                    @case ('read_count')
                    @case ('assent_count')
                    @case ('dissent_count')
                    <div class="xf-board-item-inner xf-item__{{ $columnName }} xf-item-detail">
                        <span
                            class="xf-item-inner__text xf-mo-display-in">{{ xe_trans('board::' . $columnName) }}</span>
                        <span class="xf-item-inner__text">{{ number_format($item->{$columnName}) }}</span>
                    </div>
                    @break

                    @default
                    @if (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) !== null)
                        <div
                            class="xf-board-item-inner xf-item__dynamic_field xf-item__dynamic_field-{{ $columnName }} xf-item-detail">
                            <span
                                class="xe-hidden-pc">{{ xe_trans($dynamicFieldsById[$columnName]->get('label')) }}</span>
                            {!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}
                        </div>
                    @else
                        <div class="xf-board-item-inner xf-item-detail">
                            <span class="xf-item-inner__text">{!! $item->{$columnName} !!}</span>
                        </div>
                    @endif
                    @break
                @endswitch
            @endforeach
        </li>
    @endforeach

<!-- 일반 리스트 -->
    @foreach ($paginate as $item)
        <li class="xf-board-item">
            @foreach ($skinConfig['skinListColumns'] as $columnName)
                @if (in_array($columnName, $config->get('listColumns', [])) === false)
                    @continue;
                @endif

                @switch ($columnName)
                    @case ('favorite')
                    @if (Auth::check() === true)
                        <div class="xf-board-item-inner xf-item__bookmark">
                            <span class="xf-item-inner__text blind">북마크</span>
                            <a href="#" data-url="{{ $urlHandler->get('favorite', ['id' => $item->id]) }}"
                               class="xf-item__bookmark-icon @if ($item->favorite !== null) on @endif __xe-bd-favorite"
                               title="{{xe_trans('board::favorite')}}">
                                <div class="bookmark"></div>
                            </a>
                        </div>
                    @endif
                    @break

                    @case ('title')
                    @if ($config->get('category') === true)
                        <div class="xf-board-item-inner xf-item__category">
                            <span class="xf-item-inner__text">
                                {!! $item->boardCategory !== null ? xe_trans($item->boardCategory->categoryItem->word) : '' !!}
                            </span>
                        </div>
                    @endif
                    <div class="xf-board-item-inner xf-item__title">
                        <a href="{{ $urlHandler->getShow($item, Request::all()) }}"
                           class="xf-board-item__link xf-a">
                            @if ($item->display === $item::DISPLAY_SECRET)
                                <div class="xf-secret-icon"></div>
                            @endif
                            <span class="xf-item-inner__text">
                                @if ($item->data->title_head !== null)
                                    <span
                                        class="xf-title-head xf-title-head-{{$item->data->title_head}}">[{{$item->data->title_head}}]</span>
                                @endif
                                <span class="xf-item__title-text">{{ $item->title }}</span>
                            </span>
                            <div class="xf-item-icon-box">
                                @if($item->comment_count > 0)
                                    <span
                                        class="xf-comment_count xf-ml03">[{{ number_format($item->comment_count) }}]</span>
                                @endif
                                <ul class="xf-item-icon-list xf-list">
                                    @if ($item->data->file_count > 0)
                                        <li class="xf-item-icon xf-attached-file xf-ml03"></li>
                                    @endif
                                    @if ($item->isNew($config->get('newTime')) && array_get($skinConfig, 'visibleIndexNewIcon', 'show') === 'show')
                                        <li class="xf-item-icon xf-new xf-ml03"></li>
                                    @endif
                                </ul>
                            </div>
                        </a>
                    </div>
                    @break
                    @case ('writer')
                    <div class="xf-board-item-inner xf-item__writer xf-item-detail">
                        @if ($item->hasAuthor() && $config->get('anonymity') === false)
                            <a href="#" class="mb_author xf-a xf-item__writer-link"
                               data-toggle="xe-page-toggle-menu"
                               data-url="{{ route('toggleMenuPage') }}"
                               data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                @if (array_get($skinConfig, 'visibleIndexDefaultProfileImage', 'on') === 'on')
                                    <div class="xf-writer-profile-box xf-pc-display-bl xf-mr08">
                                        <div class="xf-writer-profile-img"
                                             style="background-image: url({{ $item->user->getProfileImage() }});">
                                            <span
                                                class="blind">유저 이미지</span>
                                        </div>
                                    </div>
                                @endif
                                <span
                                    class="xf-item-inner__text">{{ $item->writer }}</span>
                            </a>
                        @else
                            @if (array_get($skinConfig, 'visibleIndexDefaultProfileImage', 'on') === 'on')
                                <span class="xe-list-board-list__user-image xe-hidden-mobile"><span
                                        class="blind">유저 이미지</span></span>
                            @endif
                            <span
                                class="xf-item-inner__text">{{ $item->writer }}</span>
                        @endif
                    </div>
                    @break

                    @case ('created_at')
                    @case ('updated_at')
                    @case ('published_at')
                    <div class="xf-board-item-inner xf-item__{{ $columnName }} xf-item-detail">
                        <span
                            class="xf-item-inner__text xf-mo-display-in">{{ xe_trans('board::' . $columnName) }}</span>
                        <span class="xf-item-inner__text">{{ $item->{$columnName}->format('Y. m. d.') }}</span>
                    </div>
                    @break

                    @case ('read_count')
                    @case ('assent_count')
                    @case ('dissent_count')
                    <div class="xf-board-item-inner xf-item__{{ $columnName }} xf-item-detail">
                        <span
                            class="xf-item-inner__text xf-mo-display-in">{{ xe_trans('board::' . $columnName) }}</span>
                        <span class="xf-item-inner__text">{{ number_format($item->{$columnName}) }}</span>
                    </div>
                    @break

                    @default
                    @if (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) !== null)
                        <div
                            class="xf-board-item-inner xf-item__dynamic_field xf-item__dynamic_field-{{ $columnName }} xf-item-detail">
                            <span
                                class="xf-item-inner__text xf-mo-display-in">{{ xe_trans($dynamicFieldsById[$columnName]->get('label')) }}</span>
                            <span
                                class="xf-item-inner__text">{!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}</span>
                        </div>
                    @else
                        <div class="xf-board-item-inner xf-item-detail">
                            <span class="xf-item-inner__text">{!! $item->{$columnName} !!}</span>
                        </div>
                    @endif
                    @break
                @endswitch
            @endforeach
        </li>
    @endforeach

<!-- 등록된 게시물 없을 경우 -->
    @if ($paginate->total() === 0)
        <li class="xf-board__no-result">
            <span class="xf-board__text">등록된 게시물이 없습니다.</span>
        </li>
    @endif
</ul>

<div class="xf-board-management-btn-box">
    @if ($isManager === true)
        <div class="xf-management-btn">
            <a href="{{ $urlHandler->managerUrl('config', ['boardId' => $instanceId]) }}"
               class="xf-board-btn xf-extra-small xf-btn-flex" target="_blank">{{ xe_trans('xe::manage') }}</a>
        </div>
    @endif
    <div class="xf-community-btn">
        @if (Auth::check() === true && array_get($skinConfig, 'visibleIndexMyBoard', 'show') === 'show')
            @if (Request::get('user_id') === Auth::user()->getId())
                <a href="{{ $urlHandler->get('index') }}"
                   class="xf-board-btn xf-my-posts-btn xf-small xf-btn-flex active">내가 쓴 글</a>
            @else
                <a href="{{ $urlHandler->get('index', ['user_id' => Auth::user()->getId()]) }}"
                   class="xf-board-btn xf-my-posts-btn xf-small xf-btn-flex">내가 쓴 글</a>
            @endif
        @endif

        @if (array_get($skinConfig, 'visibleIndexWriteButton', 'always') !== 'hidden')
            @if (array_get($skinConfig, 'visibleIndexWriteButton', 'always') === 'always')
                <a href="{{ $urlHandler->get('create') }}"
                   class="xf-board-btn xf-write-btn xf-small xf-btn-flex">{{ xe_trans('board::writeItem') }}</a>
            @elseif (array_get($skinConfig, 'visibleIndexWriteButton', 'always') === 'permission' && $isWritable === true)
                <a href="{{ $urlHandler->get('create') }}"
                   class="xf-board-btn xf-write-btn xf-small xf-btn-flex">{{ xe_trans('board::writeItem') }}</a>
            @endif
        @endif
    </div>
</div>
<div class="xf-board-pagination-box xf-mt32 xf-mb40">
    {!! $paginate->render($_skin::view('default-pagination')) !!}
</div>

<script>
    $searchBox = $('.xf-board-form-box .xf-search-input-box');
    $searchInput = $('.xf-board-form-box .xf-search-input');

    $searchInput.focus(function() {
        $searchBox.css('border-color', '#141414');
    }).focusout(function() {
        $searchBox.css('border-color', '#e0e0e0');
    });

    jQuery(function ($) {
        $('.__xe-dropdown-form input').change(function () {
            $(this).closest('form').submit();
        });
    });
</script>
