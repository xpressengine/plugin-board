import React from 'react';
import ReactDOM from 'react-dom';

class Header extends React.Component {

	constructor(props) {
		super(props);
	}

	test(e) {
		e.preventDefault();

	}

	render() {
		return (
			<div className="board_header">
				<div className="bd_manage_area">
					<button type="button" className="xe-btn xe-btn-primary-outline bd_manage">게시글 관리</button>
				</div>
				<div className="bd_manage_area xe-visible-xs">
					<a href="#" className="btn_mng bd_sorting"><i className="xi-funnel"></i> <span className="xe-sr-only">게시글 정렬</span></a>
				</div>
				<div className="bd_btn_area">
					<ul>
						<li><a href="#" className="bd_search"><span className="xe-sr-only">검색</span><i className="xi-magnifier"></i></a></li>
						<li><a href="#/create"><span className="xe-sr-only">게시판 글쓰기</span><i className="xi-pen-o"></i></a></li>
						<li><a href="#" onClick={this.test.bind(this)}><span className="xe-sr-only">게시판 설정</span><i className="xi-cog"></i></a></li>
					</ul>
				</div>
				<div className="xe-form-inline xe-hidden-xs board-sorting-area">
					<div className="xe-dropdown">
						<button className="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">전체보기</button>
						<ul className="xe-dropdown-menu">
							<li className="on"><a href="#">전체보기</a></li>
							<li><a href="#">공지</a></li>
							<li><a href="#">기타</a></li>
						</ul>
					</div>
					<div className="xe-dropdown">
						<button className="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">최신순</button>
						<ul className="xe-dropdown-menu">
							<li className="on"><a href="#">최신순</a></li>
							<li><a href="#">조회순</a></li>
							<li><a href="#">북마크</a></li>
						</ul>
					</div>
				</div>

				<div className="bd_manage_detail">
					<div className="xe-row">
						<div className="xe-col-sm-6">
							<div className="xe-row">
								<div className="xe-col-sm-3">
									<label className="xe-control-label">선택글 복사</label>
								</div>
								<div className="xe-col-sm-9">
									<div className="xe-form-inline">
										<div className="xe-dropdown">
											<button className="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">게시판1</button>
											<ul className="xe-dropdown-menu">
												<li className="on"><a href="#">게시판1</a></li>
												<li><a href="#">게시판2</a></li>
											</ul>
										</div>
										<button type="button" className="xe-btn xe-btn-primary-outline">복사</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div className="xe-row">
						<div className="xe-col-sm-6">
							<div className="xe-row">
								<div className="xe-col-sm-3">
									<label className="xe-control-label">선택글 이동</label>
								</div>
								<div className="xe-col-sm-9">
									<div className="xe-form-inline">
										<div className="xe-dropdown">
											<button className="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">게시판1</button>
											<ul className="xe-dropdown-menu">
												<li className="on"><a href="#">게시판1</a></li>
												<li><a href="#">게시판2</a></li>
											</ul>
										</div>
										<button type="button" className="xe-btn xe-btn-primary-outline">이동</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div className="xe-row">
						<div className="xe-col-sm-6">
							<div className="xe-row">
								<div className="xe-col-sm-3">
									<label className="xe-control-label">휴지통</label>
								</div>
								<div className="xe-col-sm-9">
									<a href="#" className="xe-btn-link">게시글을 휴지통으로 이동합니다.</a>
								</div>
							</div>
						</div>
					</div>
					<div className="xe-row">
						<div className="xe-col-sm-6">
							<div className="xe-row">
								<div className="xe-col-sm-3">
									<label className="xe-control-label">삭제</label>
								</div>
								<div className="xe-col-sm-9">
									<a href="#" className="xe-btn-link">게시글을 삭제합니다.</a>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div className="bd_search_area">
					<div className="bd_search_box">
						<input type="text" className="bd_search_input" title="게시판 검색" placeholder="검색어를 입력하세요" defaultValue="" />
							<a href="#" className="bd_btn_detail" title="게시판 상세검색">상세검색</a>
					</div>
					<div className="bd_search_detail">
						<div className="bd_search_detail_option">
							<div className="xe-row">
								<div className="xe-col-sm-6">
									<div className="xe-row">
										<div className="xe-col-sm-3">
											<label className="xe-control-label">카테고리</label>
										</div>
										<div className="xe-col-sm-9">
											<div className="xe-dropdown">
												<button className="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">전체보기</button>
												<ul className="xe-dropdown-menu">
													<li><a href="#">전체보기</a></li>
													<li><a href="#">카테고리</a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<div className="xe-col-sm-6">
									<div className="xe-row">
										<div className="xe-col-sm-3">
											<label className="xe-control-label">제목 + 내용</label>
										</div>
										<div className="xe-col-sm-9">
											<input type="text" className="xe-form-control" title="제목+내용" defaultValue="" />
										</div>
									</div>
								</div>
							</div>
							<div className="xe-row">
								<div className="xe-col-sm-6">
									<div className="xe-row">
										<div className="xe-col-sm-3">
											<label className="xe-control-label">글쓴이</label>
										</div>
										<div className="xe-col-sm-9">
											<input type="text" className="xe-form-control" title="제목+내용" defaultValue="" />
										</div>
									</div>
								</div>
								<div className="xe-col-sm-6">
									<div className="xe-row">
										<div className="xe-col-sm-3">
											<label className="xe-control-label">기간</label>
										</div>
										<div className="xe-col-sm-9">
											<div className="xe-form-group">
												<div className="xe-dropdown">
													<button className="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">1주</button>
													<ul className="xe-dropdown-menu">
														<li><a href="#">2주</a></li>
														<li><a href="#">1개월</a></li>
														<li><a href="#">3개월</a></li>
														<li><a href="#">6개월</a></li>
														<li><a href="#">1년</a></li>
													</ul>
												</div>
											</div>
											<div className="xe-form-inline">
												<input type="text" className="xe-form-control" title="시작 날짜 입력" value="20150928" defaultValue="20150928"/> - <input type="text" className="xe-form-control" title="끝 날짜 입력" value="20151004" defaultValue="20151004" />
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div className="bd_search_footer">
							<div className="xe-pull-right">
								<button type="button" className="xe-btn xe-btn-primary-outline">검색</button>
								<button type="button" className="xe-btn xe-btn-secondary">취소</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		);
	}
}

export default Header;