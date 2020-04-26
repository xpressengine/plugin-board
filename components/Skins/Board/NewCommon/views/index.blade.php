{{ XeFrontend::css('plugins/board/assets/css/new-board.css')->load() }}

<section class="xe-list-board container">
    <div class="xe-list-board-header">
        <div class="xe-list-board-header__title-content">
            <div class="xe-list-board-header__title-box">
                <h2 class="xe-list-board-header__title">New Theme</h2>
                <span class="xe-list-board-header__post-count">(62)</span>
            </div>
            <div class="xe-list-board-header__write-button">
                <a href="#"><img src="../../assets/img/pencil.svg"></a>
            </div>
        </div>
        <div class="xe-list-board-header__text">상단 공통내용을 사용하여 글을 입력하였을 경우 이영역을 사용하게 됩니다. 영역은 100%를 활용하며 게시판 설정에서 사용여부를
            통해 사용할 수 있습니다. </div>
        <div class="xe-list-board-header__contents">
            <div class="xe-list-board-header--left-box">
                <div class="xe-list-board--header__search">
                    <input type="text" name="search" class="xe-list-board--header__search__control">
                    <span class="xe-list-board--header__search__icon">
                        <i class="xi-search"></i>
                    </span>
                </div>
            </div>
            <div class="xe-list-board-header--right-box">
                <div class="xe-list-board-header--category xe-list-board-header--dropdown">
                    <button type="button"
                        class="xe-list-board-header--category__button xe-list-board-header--dropdown__button"
                        data-toggle="xe-list-board--header--category__button-dropdown">
                        <span class="xe-list-board--header-category__button-text">전체 카테고리</span>
                        <i class="xi-angle-down-thin"></i>
                    </button>
                    <ul class="xe-list-board-header--category-menu xe-list-board-header--dropdown-menu">
                        <li class="xe-list-board-header--dropdown-menu-item on">
                            <a href="#">전체 카테고리</a>
                        </li>
                        <li class="xe-list-board-header--dropdown-menu-item">
                            <a href="#">공지사항</a>
                        </li>
                        <li class="xe-list-board-header--dropdown-menu-item">
                            <a href="#">릴리즈 노트</a>
                        </li>
                    </ul>
                </div>
                <div class="xe-list-board-header--sort xe-list-board-header--dropdown">
                    <button type="button"
                        class="xe-list-board-header--category__button xe-list-board-header--dropdown__button"
                        data-toggle="xu-dropdown">
                        <span class="xu-button__text">최신순</span>
                        <i class="xi-angle-down-thin"></i>
                    </button>
                    <ul class="xe-list-board-header--sort-menu xe-list-board-header--dropdown-menu">
                        <li class="xe-list-board-header--dropdown-menu-item on">
                            <a href="#">최신순</a>
                        </li>
                        <li class="xe-list-board-header--dropdown-menu-item">
                            <a href="#">추천순</a>
                        </li>
                        <li class="xe-list-board-header--dropdown-menu-item">
                            <a href="#">최근 수정순</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="xe-list-board-body">
        <ul class="xe-list-board-list--item xe-list-board-list">
            <li class="xe-list-board-list--header">
                <div class="xe-list-board-list__favorite"><i class="xi-star-o"></i></div>
                <div class="xe-list-board-list__category">카테고리</div>
                <div class="xe-list-board-list__subject">제목</div>
                <div class="xe-list-board-list__writter">작성자</div>
                <div class="xe-list-board-list__created_date">작성일</div>
                <div class="xe-list-board-list__updated_date">수정일</div>
                <div class="xe-list-board-list__assent-count">추천수</div>
                <div class="xe-list-board-list__dissent-count">비추천수</div>
                <div class="xe-list-board-list__view">조회수</div>
                @foreach ($skinConfig['listColumns'] as $columnName)
                    @if (in_array($columnName, ['favorite', 'title', 'writer', 'assent_count', 'read_count', 'created_at', 'updated_at', 'dissent_count']) === true)
                        @continue
                    @endif
                    <div class="xe-list-board-list__dynamic-field">{{ xe_trans($dynamicFieldsById[$columnName]->get('label')) }}</div>
                @endforeach
            </li>
            @foreach ($paginate as $item)
                <li class="xe-list-board-list--item">
                    <div class="xe-list-board-list__favorite xe-hidden-mobile"><i class="xi-star"></i></div>
                    <div class="xe-list-board-list__category">카테고리</div>
                    <div class="xe-list-board-list__subject">
                        <a href="#">
                            <span class="xe-list-board-list__notice--box-form">공지</span>
                            <span class="xe-list-board-list__secret"><i class="xi-lock"><span
                                        class="blind">비밀글</span></i></span>
                            {{ $item->title }}
                            <span class="xe-list-board-list__subject-comment xe-hidden-mobile">{{ $item->comment_comment }}</span>
                            <span class="xe-list-board-list__subject-file"><i class="xi-paperclip"></i><span
                                    class="blind">첨부파일</span></span>
                            
                            <span class="xe-list-board-list__subject-new"><span class="blind">새글</span></span>
                        </a>
                    </div>
                    <div class="xe-list-board-list__writter">
                        <a href="#">
                            <span class="xe-list-board-list__user-image xe-hidden-mobile"><span class="blind">유저
                                    이미지</span></span>
                            <span class="xe-list-board-list__nickname">{{ $item->writer}}</span>
                        </a>
                    </div>
                    <div class="xe-list-board-list__created_date" @if($item->created_at->getTimestamp() > strtotime('-1 month')) data-xe-timeago="{{ $item->created_at }}" @endif><span class="blind">작성일</span>{{ $item->created_at->toDateString() }}</div>
                    <div class="xe-list-board-list__updated_date" @if($item->created_at->getTimestamp() > strtotime('-1 month')) data-xe-timeago="{{ $item->updated_at }}" @endif><span class="blind">수정일</span>{{ $item->updated_at->toDateString() }}</div>
                    <div class="xe-list-board-list__assent-count xe-hidden-mobile"><span class="blind">추천</span> {{ $item->assent_count }}</div>
                    <div class="xe-list-board-list__dissent-count xe-hidden-mobile"><span class="blind">비추천</span> {{ $item->dissent_count }}</div>

                    <div class="xe-list-board-list__view"><span class="xe-hidden-pc">조회</span> {{ number_format($item->read_count) }} </div>
                    <div class="xe-list-board-list__comment xe-hidden-pc"><span>댓글</span> {{ number_format($item->comment_count) }}</div>
                    @foreach ($skinConfig['listColumns'] as $columnName)
                        @if (in_array($columnName, ['favorite', 'title', 'writer', 'assent_count', 'read_count', 'created_at', 'updated_at', 'dissent_count']) === true)
                            @continue
                        @endif

                        @php
                            $fieldType = XeDynamicField::get($config->get('documentGroup'), $columnName);
                        @endphp
                        
                        <div class="xe-list-board-list__dynamic-field"><span class="xe-hidden-pc xe-list-board-list__dynamic-field-title">{{ xe_trans($dynamicFieldsById[$columnName]->get('label')) }}</span> {!! $fieldType->getSkin()->output($columnName, $item->getAttributes()) !!}</div>
                    @endforeach

                    <div class="xe-list-board-list__vote-count xe-hidden-pc">
                        @if (in_array('assent_count', $skinConfig['listColumns']) === true)
                            <div class="xe-list-board-list__assent-count"><span class="blind">추천</span><i
                                    class="xi-thumbs-up"></i> {{ $item->assent_count }}</div>
                        @endif
                        @if (in_array('dissent_count', $skinConfig['listColumns']) === true)
                            <div class="xe-list-board-list__dissent-count"><span class="blind">비추천</span><i
                                    class="xi-thumbs-down"></i> {{ $item->dissent_count }}</div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="xe-list-board-footer">
        <div class="xe-list-board--button-box">
            <div class="xe-list-board--btn-left-box">
                <a href="#" class="xe-list-board__btn">관리</a>
            </div>
            <div class="xe-list-board--btn-right-box">
                <a href="#" class="xe-list-board__btn xe-list-board__btn-primary">내가 쓴 글</a>
                <a href="#" class="xe-list-board__btn xe-list-board__btn-primary">글쓰기</a>
            </div>
        </div>
        <div class="xe-list-board--pg">
            <span class="xe-list-board__btn_pg xe-list-board__btn_prev"><i class="xi-angle-left"></i></span>
            <span class="xe-list-board__pg-number xe-list-board__pg-number--active">1</span>
            <span class="xe-list-board__pg-number"><a href="#">2</a></span>
            <span class="xe-list-board__pg-number"><a href="#">3</a></span>
            <span class="xe-list-board__pg-number"><a href="#">4</a></span>
            <span class="xe-list-board__pg-number"><a href="#">5</a></span>
            <span class="xe-list-board__pg-number"><a href="#">6</a></span>
            <span class="xe-list-board__pg-number"><a href="#">7</a></span>
            <span class="xe-list-board__pg-number"><a href="#">8</a></span>
            <span class="xe-list-board__pg-number"><a href="#">9</a></span>
            <span class="xe-list-board__pg-number"><a href="#">10</a></span>
            <span class="xe-list-board__btn_pg xe-list-board__btn_next"><i class="xi-angle-right"></i></span>
        </div>
    </div>
    <div class="xe-list-board-footer__text">
        하단 공통내용을 사용하여 글을 입력하였을 경우 이영역을 사용하게 됩니다. 영역은 100%를 활용하며 게시판 설정에서 사용여부를 통해 사용할 수 있습니다.
    </div>
</section>