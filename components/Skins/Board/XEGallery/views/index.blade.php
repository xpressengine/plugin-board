{{ XeFrontend::css('plugins/board/assets/css/xe-board-gallery.css')->load() }}

<form method="get" action="{{ $urlHandler->get('index') }}" class="__xe_search">
    <div class="xf-board-form-box">
        <div class="xf-board-search-box">
            <div class="xf-search-input-box">
                <input class="xf-search-input" type="text" placeholder="검색어를 입력하세요" name="title_pure_content"
                       value="{{ Request::get('title_pure_content') }}">
            </div>
            <div class="xf-search-btn-box">
                <button class="xf-search-btn xf-board-btn" type="submit"></button>
            </div>
        </div>
        <div class="xf-board-dropdown-box">
            @if ($config->get('category') === true)
                <div class="xf-board-dropdown">
                    {!! uio('uiobject/board@new_select', [
                        'name' => 'category_item_id',
                        'label' => xe_trans('xe::category'),
                        'value' => Request::get('category_item_id'),
                        'items' => $categories
                    ]) !!}
                </div>
            @endif
            <div class="xf-board-dropdown">
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

<ul class="xf-board-list xf-list @if (array_get($skinConfig, 'visibleIndexWebzineMobileType', 'double') === 'double') xf-col2 @endif">
    @foreach ($notices as $item)
        <li class="xf-board-item">
            <div class="xf-board-item-inner">
                <a href="{{$urlHandler->getShow($item, Request::all())}}" class="xf-board-item__link xf-a">
                    <div class="xf-thumbnail-box xf-mb14">
                        <div class="xf-thumbnail-img"
                             style="background-image:url({{ $item->board_thumbnail_path }});"></div>
                        <div class="xf-thumbnail-notice">
                            <span class="xf-notice__text">{{ xe_trans('xe::notice') }}</span>
                        </div>
                    </div>
                </a>
                <div class="xf-detail-box">
                    <a href="{{$urlHandler->getShow($item, Request::all())}}" class="xf-board-item__link xf-a">
                        <div class="xf-post-contents xf-mb16">
                            @if ($config->get('category') === true && $item->boardCategory !== null)
                                <div class="xf-post-category">
                                    <span
                                        class="xf-post__text">{!! xe_trans($item->boardCategory->categoryItem->word) !!}</span>
                                </div>
                            @endif
                            <div class="xf-post-title xf-ellipsis2 xf-mb10">
                                @if ($item->display === $item::DISPLAY_SECRET)
                                    <div class="xf-secret-icon"></div>
                                @endif
                                @if ($item->data->title_head !== null)
                                    <span class="xf-title-head xf-title-head-{{$item->data->title_head}}">[{{$item->data->title_head}}]</span>
                                @endif
                                <strong class="xf-post-title__text">
                                    {{ $item->title }}
                                </strong>
                                <div class="xf-item-icon-box">
                                    <ul class="xf-item-icon-list xf-list">
                                        @if ($item->data->file_count > 0)
                                            <li class="xf-item-icon xf-attached-file"></li>
                                        @endif
                                        @if ($item->isNew($config->get('newTime')) && array_get($skinConfig, 'visibleIndexNewIcon', 'show') === 'show')
                                            <li class="xf-item-icon xf-new"></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            @if ($item->display !== $item::DISPLAY_SECRET && array_get($skinConfig, 'visibleIndexGalleryDescription', 'on') === 'on')
                                <p class="xf-post-text xf-p xf-ellipsis2">{{ $item->pure_content }}</p>
                            @endif
                        </div>
                    </a>
                    <div class="xf-post-info">

                        @if (in_array('writer', $skinConfig['listColumns']) === true && array_get($skinConfig, 'visibleIndexGalleryProfileImage', 'on') === 'on')
                            @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                <div class="xf-profile-img-box">
                                    <a href="#" class="xf-a xf-item__writer-link"
                                       data-toggle="xe-page-toggle-menu"
                                       data-url="{{ route('toggleMenuPage') }}"
                                       data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                        <div class="xf-writer-profile-box xf-mr08">
                                            <div class="xf-writer-profile-img"
                                                 style="background-image: url({{ $item->user->getProfileImage() }});"></div>
                                        </div>
                                    </a>
                                </div>
                            @else
                                <div class="xf-profile-img-box">
                                    <a href="#" class="xf-a xf-item__writer-link"
                                       data-toggle="xe-page-toggle-menu"
                                       data-url="{{ route('toggleMenuPage') }}"
                                       data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                        <div class="xf-writer-profile-box xf-mr08">
                                            <div class="xf-writer-profile-img"
                                                 style="background-image: url('/assets/core/user/img/default_avatar.jpg')"></div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endif
                        <div class="xf-detail-info">
                            @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                <a href="#" class="xf-a xf-item__writer-link xf-mb06"
                                   data-toggle="xe-page-toggle-menu"
                                   data-url="{{ route('toggleMenuPage') }}"
                                   data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                    <span class="xf-writer__nickname">{{ $item->writer }}</span>
                                </a>
                            @else
                                <span class="xf-writer__nickname">{{ $item->writer }}</span>
                            @endif
                            <ul class="xf-list xf-info-list">
                                @if (in_array('read_count', $skinConfig['listColumns']) === true)
                                    <li class="xf-info-item">
                                        <span class="xf-info-item__text">{{ xe_trans('board::read_count') }}</span>
                                        <span class="xf-info-item__text">{{ number_format($item->read_count) }}</span>
                                    </li>
                                @endif

                                @if (in_array('created_at', $skinConfig['listColumns']) === true)
                                    <li class="xf-info-item">
                                        <span
                                            class="xf-info-item__text blind">{{ xe_trans('board::created_at') }}</span>
                                        <span
                                            class="xf-info-item__text">{{ $item->created_at->format('Y. m. d.') }}</span>
                                    </li>
                                @endif

                                @if (in_array('updated_at', $skinConfig['listColumns']) === true)
                                    <li class="xf-info-item">
                                        <span
                                            class="xf-info-item__text blind">{{ xe_trans('board::updated_at') }}</span>
                                        <span
                                            class="xf-info-item__text">{{ $item->updated_at->format('Y. m. d.') }}</span>
                                    </li>
                                @endif

                                {{--                                <li class="xf-info-item">--}}
                                {{--                                    <span class="xf-info-item__text">조회</span>--}}
                                {{--                                    <span class="xf-info-item__text">1</span>--}}
                                {{--                                </li>--}}
                            </ul>
                        </div>
                        <div class="xf-assent-box">
                            <ul class="xf-assent-list xf-list">
                                @if (in_array('assent_count', $skinConfig['listColumns']) === true)
                                    <li class="xf-assent-item">
                                        <div class="xf-assent xf-assent-icon"></div>
                                        <span class="blind">{{ xe_trans('board::assent_count') }}</span>
                                        <div class="xf-assent-text">{{ number_format($item->assent_count) }}</div>
                                    </li>
                                @endif
                                @if (in_array('dissent_count', $skinConfig['listColumns']) === true)
                                    <li class="xf-assent-item">
                                        <div class="xf-dissent xf-assent-icon"></div>
                                        <span class="blind">{{ xe_trans('board::dissent_count') }}</span>
                                        <div class="xf-assent-text">{{ number_format($item->dissent_count) }}</div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    @endforeach

    @foreach ($paginate as $item)
        <li class="xf-board-item">
            <div class="xf-board-item-inner">
                <a href="{{$urlHandler->getShow($item, Request::all())}}" class="xf-board-item__link xf-a">
                    <div class="xf-thumbnail-box xf-mb14">
                        <div class="xf-thumbnail-img"
                             style="background-image:url({{ $item->board_thumbnail_path }});"></div>
                    </div>
                </a>
                <div class="xf-detail-box">
                    <a href="{{$urlHandler->getShow($item, Request::all())}}" class="xf-board-item__link xf-a">
                        <div class="xf-post-contents xf-mb16">
                            @if ($config->get('category') === true && $item->boardCategory !== null)
                                <div class="xf-post-category">
                                    <span
                                        class="xf-post__text">{!! xe_trans($item->boardCategory->categoryItem->word) !!}</span>
                                </div>
                            @endif
                            <div class="xf-post-title xf-ellipsis2 xf-mb10">
                                @if ($item->display === $item::DISPLAY_SECRET)
                                    <div class="xf-secret-icon"></div>
                                @endif

                                @if ($item->data->title_head !== null)
                                    <span class="xf-title-head xf-title-head-{{$item->data->title_head}}">[{{$item->data->title_head}}]</span>
                                @endif
                                <strong class="xf-post-title__text">{{ $item->title }}</strong>
                                <div class="xf-item-icon-box">
                                    <ul class="xf-item-icon-list xf-list">
                                        @if ($item->data->file_count > 0)
                                            <li class="xf-item-icon xf-attached-file"></li>
                                        @endif
                                        @if ($item->isNew($config->get('newTime')) && array_get($skinConfig, 'visibleIndexNewIcon', 'show') === 'show')
                                            <li class="xf-item-icon xf-new"></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            @if ($item->display !== $item::DISPLAY_SECRET && array_get($skinConfig, 'visibleIndexGalleryDescription', 'on') === 'on')
                                <p class="xf-post-text xf-p xf-ellipsis2">{{ $item->pure_content }}</p>
                            @endif
                        </div>
                    </a>
                    <div class="xf-post-info">
                        @if (in_array('writer', $skinConfig['listColumns']) === true && array_get($skinConfig, 'visibleIndexGalleryProfileImage', 'on') === 'on')
                            @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                <div class="xf-profile-img-box">
                                    <a href="#" class="xf-a xf-item__writer-link"
                                       data-toggle="xe-page-toggle-menu"
                                       data-url="{{ route('toggleMenuPage') }}"
                                       data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                        <div class="xf-writer-profile-box xf-mr08">
                                            <div class="xf-writer-profile-img"
                                                 style="background-image: url({{ $item->user->getProfileImage() }});"></div>
                                        </div>
                                    </a>
                                </div>
                            @else
                                <div class="xf-profile-img-box">
                                    <a href="#" class="xf-a xf-item__writer-link"
                                       data-toggle="xe-page-toggle-menu"
                                       data-url="{{ route('toggleMenuPage') }}"
                                       data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                        <div class="xf-writer-profile-box xf-mr08">
                                            <div class="xf-writer-profile-img"
                                                 style="background-image: url('/assets/core/user/img/default_avatar.jpg')"></div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endif
                        <div class="xf-detail-info">
                            @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                <a href="#" class="xf-a xf-item__writer-link xf-mb06"
                                   data-toggle="xe-page-toggle-menu"
                                   data-url="{{ route('toggleMenuPage') }}"
                                   data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                    <span class="xf-writer__nickname">{{ $item->writer }}</span>
                                </a>
                            @else
                                <span class="xf-writer__nickname">{{ $item->writer }}</span>
                            @endif
                            <ul class="xf-list xf-info-list">
                                @if (in_array('read_count', $skinConfig['listColumns']) === true)
                                    <li class="xf-info-item">
                                        <span class="xf-info-item__text">{{ xe_trans('board::read_count') }}</span>
                                        <span class="xf-info-item__text">{{ number_format($item->read_count) }}</span>
                                    </li>
                                @endif

                                @if (in_array('created_at', $skinConfig['listColumns']) === true)
                                    <li class="xf-info-item">
                                        <span
                                            class="xf-info-item__text blind">{{ xe_trans('board::created_at') }}</span>
                                        <span
                                            class="xf-info-item__text">{{ $item->created_at->format('Y. m. d.') }}</span>
                                    </li>
                                @endif

                                @if (in_array('updated_at', $skinConfig['listColumns']) === true)
                                    <li class="xf-info-item">
                                        <span
                                            class="xf-info-item__text blind">{{ xe_trans('board::updated_at') }}</span>
                                        <span
                                            class="xf-info-item__text">{{ $item->updated_at->format('Y. m. d.') }}</span>
                                    </li>
                                @endif

                                {{--                                <li class="xf-info-item">--}}
                                {{--                                    <span class="xf-info-item__text">조회</span>--}}
                                {{--                                    <span class="xf-info-item__text">1</span>--}}
                                {{--                                </li>--}}
                            </ul>
                        </div>
                        <div class="xf-assent-box">
                            <ul class="xf-assent-list xf-list">
                                @if (in_array('assent_count', $skinConfig['listColumns']) === true)
                                    <li class="xf-assent-item">
                                        <div class="xf-assent xf-assent-icon"></div>
                                        <span class="blind">{{ xe_trans('board::assent_count') }}</span>
                                        <div class="xf-assent-text">{{ number_format($item->assent_count) }}</div>
                                    </li>
                                @endif
                                @if (in_array('dissent_count', $skinConfig['listColumns']) === true)
                                    <li class="xf-assent-item">
                                        <div class="xf-dissent xf-assent-icon"></div>
                                        <span class="blind">{{ xe_trans('board::dissent_count') }}</span>
                                        <div class="xf-assent-text">{{ number_format($item->dissent_count) }}</div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
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
    jQuery(function ($) {
        $('.__xe-dropdown-form input').change(function () {
            $(this).closest('form').submit();
        });
    });
</script>
