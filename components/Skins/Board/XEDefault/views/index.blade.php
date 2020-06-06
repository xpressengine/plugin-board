{{ XeFrontend::css('plugins/board/assets/css/new-board.css')->load() }}

<div class="xe-list-board-header__contents">
    <form method="get" action="{{ $urlHandler->get('index') }}" class="__xe_search">
        <div class="xe-list-board-header--left-box">
            <div class="xe-list-board--header__search">
                <input type="text" name="title_content" class="xe-list-board--header__search__control" value="{{ Request::get('title_content') }}">
                <span class="xe-list-board--header__search__icon">
                    <button type="submit"><i class="xi-search"></i></button>
                </span>
            </div>
        </div>
        <div class="xe-list-board-header--right-box __xe-forms">
            @if ($config->get('category') === true)
                <div class="xe-list-board-header--category xe-list-board-header--dropdown-box">
                    <div class="xe-list-board-header--dropdown">
                        <div class="xe-list-board-header-category__button xe-list-board-header--dropdown__button">
                            {!! uio('uiobject/board@new_select', [
                                'name' => 'category_item_id',
                                'label' => xe_trans('xe::category'),
                                'value' => Request::get('category_item_id'),
                                'items' => $categories
                            ]) !!}
                        </div>
                    </div>
                </div>
            @endif
            <div class="xe-list-board-header--sort xe-list-board-header--dropdown-box">
                <div class="xe-list-board-header--dropdown">
                    <div class="xe-list-board-header-order__button xe-list-board-header--dropdown__button">
                        {!! uio('uiobject/board@new_select', [
                            'name' => 'order_type',
                            'label' => xe_trans('xe::order'),
                            'value' => Request::get('order_type', $config->get('orderType')),
                            'items' => $handler->getOrders()
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="xe-list-board-body">
    <ul class="xe-list-board-list--item xe-list-board-list">
        <li class="xe-list-board-list--header">
            @foreach ($skinConfig['skinListColumns'] as $columnName)
                @if (in_array($columnName, $config->get('listColumns', [])) === false)
                    @continue;
                @endif
                
                @if ($columnName === 'favorite')
                    @if (Auth::check() === true)
                        <div class="xe-list-board-list__favorite xe-list-board-list__favorite-link">
                            @if (Request::has('favorite'))
                                <a href="{{ $urlHandler->get('index', Request::except(['favorite', 'page'])) }}" class="xe-list-board-list__favorite-link on">
                                    <div class="bookmark"></div>
                                </a>
                            @else
                                <a href="{{ $urlHandler->get('index', array_merge(Request::except('page'), ['favorite' => 1])) }}" class="xe-list-board-list__favorite-link">
                                    <div class="bookmark"></div>
                                </a>
                            @endif
                        </div>
                    @endif
                @elseif ($columnName === 'title')
                    @if ($config->get('category') === true)
                        <div class="xe-list-board-list__category">{{ xe_trans('board::category') }}</div>
                    @endif
                    <div class="xe-list-board-list__title">{{ xe_trans('board::title') }}</div>
                @else
                    @if (isset($dynamicFieldsById[$columnName]) === true)
                        <div class="xe-list-board-list__dynamic-field xe-list-board-list__dynamic-field-{{ $columnName }}">{{ xe_trans($dynamicFieldsById[$columnName]->get('label')) }}</div> 
                    @else
                        <div class="xe-list-board-list__{{ $columnName }}">{{ xe_trans('board::' . $columnName) }}</div>
                    @endif
                @endif
            @endforeach
        </li>

        @foreach ($notices as $item)
            <li class="xe-list-board-list--item xe-list-board-list--item-notice">
                @foreach ($skinConfig['skinListColumns'] as $columnName)
                    @if (in_array($columnName, $config->get('listColumns', [])) === false)
                        @continue;
                    @endif
                
                    @switch ($columnName)
                        @case ('favorite')
                            @if (Auth::check() === true)
                                <div class="xe-list-board-list__favorite xe-hidden-mobile">
                                    <a href="#" data-url="{{ $urlHandler->get('favorite', ['id' => $item->id]) }}" class="xe-list-board-list__favorite-link @if ($item->favorite !== null) on @endif __xe-bd-favorite"  title="{{xe_trans('board::favorite')}}">
                                        <div class="bookmark"></div>
                                    </a>
                                </div>
                            @endif
                            @break

                        @case ('title')
                            @if ($config->get('category') === true)
                                <div class="xe-list-board-list__category">
                                    <span class="xe-list-board-list__text">
                                        {!! $item->boardCategory !== null ? xe_trans($item->boardCategory->categoryItem->word) : '' !!}
                                    </span>
                                </div>
                            @endif
                            <div class="xe-list-board-list__title">
                                <a href="{{ $urlHandler->getShow($item, Request::all()) }}" class="xe-list-board-list__title-link">
                                    <span class="xe-list-board-list__notice--box-form"><span class="xe-list-board-list__notice--box-form-bg">공지</span></span>
                                    @if ($item->display == $item::DISPLAY_SECRET)
                                        <span class="xe-list-board-list__subjec-secret"><i class="xi-lock"></i></span>
                                    @endif
                                    <span class="xe-list-board-list__title-text"><span>{{ $item->title }}</span></span>
                                    <div class="xe-list-board-list__title-icon">
                                        @if($item->comment_count > 0)
                                            <span class="xe-list-board-list__title-comment_count">{{ number_format($item->comment_count) }}</span>
                                        @endif
                                        @if ($item->data->file_count > 0)
                                            <span class="xe-list-board-list__title-file"><i class="xi-paperclip"></i><span class="blind">첨부파일</span></span>
                                        @endif
                                        @if ($item->isNew($config->get('newTime')) && array_get($skinConfig, 'visibleIndexNewIcon', 'show') === 'show')
                                            <span class="xe-list-board-list__title-new"><span class="blind">새글</span></span>
                                        @endif
                                    </div>
                                </a>
                            </div>
                            @break
                        @case ('writer')
                            <div class="xe-list-board-list__writer">
                                @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                    <a href="#" class="mb_author list-board__color-gray"
                                       data-toggle="xe-page-toggle-menu"
                                       data-url="{{ route('toggleMenuPage') }}"
                                       data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                        @if (array_get($skinConfig, 'visibleIndexDefaultProfileImage', 'on') === 'on')
                                            <span class="xe-list-board-list__user-image xe-hidden-mobile" style="background: url({{ $item->user->getProfileImage() }}); background-size: 28px;"><span class="blind">유저 이미지</span></span>
                                        @endif
                                        <span class="xe-list-board-list__display_name xe-list-board-list__mobile-style">{{ $item->writer }}</span>
                                    </a>
                                @else
                                    @if (array_get($skinConfig, 'visibleIndexDefaultProfileImage', 'on') === 'on')
                                        <span class="xe-list-board-list__user-image xe-hidden-mobile"><span class="blind">유저 이미지</span></span>
                                    @endif
                                    <span class="xe-list-board-list__display_name xe-list-board-list__mobile-style">{{ $item->writer }}</span>
                                @endif
                            </div>
                            @break

                        @case ('created_at')
                        @case ('updated_at')
                        @case ('published_at')
                            <div class="xe-list-board-list__{{ $columnName }} xe-list-board-list__mobile-style"><span class="blind">{{ xe_trans('board::' . $columnName) }}</span> {{ $item->{$columnName}->format('Y. m. d.') }}</div>
                            @break

                        @case ('read_count')
                        @case ('assent_count')
                        @case ('dissent_count')
                            <div class="xe-list-board-list__{{ $columnName }} xe-list-board-list__mobile-style"><span class="xe-hidden-pc">{{ xe_trans('board::' . $columnName) }}</span> {{ number_format($item->{$columnName}) }}</div>
                            @break

                        @default
                            @if (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) !== null)
                                <div class="xe-list-board-list__dynamic-field xe-list-board-list__dynamic-field-{{ $columnName }} xe-list-board-list__mobile-style">
                                    <span class="xe-hidden-pc">{{ xe_trans($dynamicFieldsById[$columnName]->get('label')) }}</span>
                                    {!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}
                                </div>
                            @else
                                {!! $item->{$columnName} !!}
                            @endif
                            @break
                    @endswitch
                @endforeach
            </li>
        @endforeach
        
        @foreach ($paginate as $item)
            <li class="xe-list-board-list--item">
                @foreach ($skinConfig['skinListColumns'] as $columnName)
                    @if (in_array($columnName, $config->get('listColumns', [])) === false)
                        @continue;
                    @endif
                    
                    @switch ($columnName)
                        @case ('favorite')
                        @if (Auth::check() === true)
                            <div class="xe-list-board-list__favorite xe-hidden-mobile">
                                <a href="#" data-url="{{ $urlHandler->get('favorite', ['id' => $item->id]) }}" class="xe-list-board-list__favorite-link @if ($item->favorite !== null) on @endif __xe-bd-favorite"  title="{{xe_trans('board::favorite')}}">
                                <div class="bookmark"></div>
                                </a>
                            </div>
                        @endif
                        @break

                        @case ('title')
                        @if ($config->get('category') === true)
                            <div class="xe-list-board-list__category">
                                    <span class="xe-list-board-list__text">
                                        {!! $item->boardCategory !== null ? xe_trans($item->boardCategory->categoryItem->word) : '' !!}
                                    </span>
                            </div>
                        @endif
                        <div class="xe-list-board-list__title">
                            <a href="{{ $urlHandler->getShow($item, Request::all()) }}" class="xe-list-board-list__title-link">
                                @if ($item->display == $item::DISPLAY_SECRET)
                                    <span class="xe-list-board-list__subjec-secret"><i class="xi-lock"></i></span>
                                @endif
                                <span class="xe-list-board-list__title-text"><span>{{ $item->title }}</span></span>
                                <div class="xe-list-board-list__title-icon">
                                    @if($item->comment_count > 0)
                                        <span class="xe-list-board-list__title-comment_count">{{ number_format($item->comment_count) }}</span>
                                    @endif
                                    @if ($item->data->file_count > 0)
                                        <span class="xe-list-board-list__title-file"><i class="xi-paperclip"></i><span class="blind">첨부파일</span></span>
                                    @endif
                                    @if ($item->isNew($config->get('newTime')) && array_get($skinConfig, 'visibleIndexNewIcon', 'show') === 'show')
                                        <span class="xe-list-board-list__title-new"><span class="blind">새글</span></span>
                                    @endif
                                </div>
                            </a>
                        </div>
                        @break
                        @case ('writer')
                        <div class="xe-list-board-list__writer">
                            @if ($item->hasAuthor() && $config->get('anonymity') === false)
                                <a href="#" class="mb_author list-board__color-gray"
                                   data-toggle="xe-page-toggle-menu"
                                   data-url="{{ route('toggleMenuPage') }}"
                                   data-data='{!! json_encode(['id'=>$item->getUserId(), 'type'=>'user']) !!}'>
                                    @if (array_get($skinConfig, 'visibleIndexDefaultProfileImage', 'on') === 'on')
                                        <span class="xe-list-board-list__user-image xe-hidden-mobile" style="background: url({{ $item->user->getProfileImage() }}); background-size: 28px;"><span class="blind">유저 이미지</span></span>
                                    @endif
                                    <span class="xe-list-board-list__display_name xe-list-board-list__mobile-style">{{ $item->writer }}</span>
                                    
                                </a>
                            @else
                                @if (array_get($skinConfig, 'visibleIndexDefaultProfileImage', 'on') === 'on')
                                    <span class="xe-list-board-list__user-image xe-hidden-mobile"><span class="blind">유저 이미지</span></span>
                                @endif
                                <span class="xe-list-board-list__display_name xe-list-board-list__mobile-style">{{ $item->writer }}</span>
                            @endif
                        </div>
                        @break

                        @case ('created_at')
                        @case ('updated_at')
                        @case ('published_at')
                        <div class="xe-list-board-list__{{ $columnName }} xe-list-board-list__mobile-style"><span class="xe-hidden-pc">{{ xe_trans('board::' . $columnName) }}</span> {{ $item->{$columnName}->format('Y. m. d.') }}</div>
                        @break

                        @case ('read_count')
                        @case ('assent_count')
                        @case ('dissent_count')
                        <div class="xe-list-board-list__{{ $columnName }} xe-list-board-list__mobile-style"><span class="xe-hidden-pc">{{ xe_trans('board::' . $columnName) }}</span> {{ number_format($item->{$columnName}) }}</div>
                        @break

                        @default
                        @if (($fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName)) !== null)
                            <div class="xe-list-board-list__dynamic-field xe-list-board-list__dynamic-field-{{ $columnName }} xe-list-board-list__mobile-style">
                                <span class="xe-hidden-pc">{{ xe_trans($dynamicFieldsById[$columnName]->get('label')) }}</span>
                                {!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}
                            </div>
                        @else
                            <div class="xe-list-board-list__default">
                                {!! $item->{$columnName} !!}
                            </div>
                        @endif
                        @break
                    @endswitch
                @endforeach
            </li>
        @endforeach
        
        @if ($paginate->total() === 0)
            <div class="xe-list-webzine-board__no-result">
                <span class="xe-list-webzine-board__text">등록된 게시물이 없습니다.</span>
            </div>
        @endif
    </ul>
</div>

<div class="xe-list-board-footer">
    <div class="xe-list-board--button-box">
        @if ($isManager === true)
            <div class="xe-list-board--btn-left-box">
                <a href="{{ $urlHandler->managerUrl('config', ['boardId' => $instanceId]) }}" class="xe-list-board__btn xe-list-board__btn-primary" target="_blank">{{ xe_trans('xe::manage') }}</a>
            </div>
        @endif
        <div class="xe-list-board--btn-right-box">
            @if (Auth::check() === true && array_get($skinConfig, 'visibleIndexMyBoard', 'show') === 'show')
                @if (Request::get('user_id') === Auth::user()->getId())
                    <a href="{{ $urlHandler->get('index') }}" class="xe-list-board__btn active">내가 쓴 글</a>
                @else
                    <a href="{{ $urlHandler->get('index', ['user_id' => Auth::user()->getId()]) }}" class="xe-list-board__btn">내가 쓴 글</a>
                @endif
            @endif
            @if (array_get($skinConfig, 'visibleIndexWriteButton', 'always') !== 'hidden')
                @if (array_get($skinConfig, 'visibleIndexWriteButton', 'always') === 'always')
                    <a href="{{ $urlHandler->get('create') }}" class="xe-list-board__btn">{{ xe_trans('board::writeItem') }}</a>
                @elseif (array_get($skinConfig, 'visibleIndexWriteButton', 'always') === 'permission' && $isWritable === true)
                    <a href="{{ $urlHandler->get('create') }}" class="xe-list-board__btn">{{ xe_trans('board::writeItem') }}</a>
                @endif
            @endif
        </div>
    </div>
</div>

{!! $paginate->render($_skin::view('default-pagination')) !!}
