import React, { PropTypes } from 'react';

// import BoardList from './../list/BoardList';


class DetailView extends React.Component {

	static contextType = {
		query: PropTypes.object,
		params: PropTypes.object
	};

	render() {

		return (
			
			<div className="board_read">
				<div className="read_header">
					<span className="category">여행</span>
					<h1><a href="#">기요미즈데라, 교토 청수사 만년필 스케치</a></h1>
					<div className="more_info">

						<a href="#" className="mb_autohr">XE</a>
						<span className="mb_time"><i className="xi-time"></i> 15시간 전</span>
						<span className="mb_readnum"><i className="xi-eye"></i> 78</span>
						<div className="ly_popup">
							<ul>
								<li><a href="#">프로필 보기</a></li>
								<li><a href="#">쪽지 보내기</a></li>
								<li><a href="#">배포 자료 목록</a></li>
							</ul>
						</div>
					</div>
				</div>
				<div className="read_body">
					<div className="xe_content">

						<div className="__xe_contents_compiler" id="xe-editor-content">
							<h1>에디터 View 스타일</h1>
							<h2>font size</h2>
							<blockquote>
								<h1>난 h1이다 28px</h1>
								<h2>난 h2이다 26px</h2>
								<h3>난 h3이다 23px</h3>
								<h4>난 h4이다 20px</h4>
								<h5>난 h5이다 17px</h5>
								<h6>난 h6이다 16px</h6>
							</blockquote>
							<h2>common</h2>
							<p>기본 폰트 크기는 16px</p>
							<p>키이스트·FNC·SM ‘1%’대 상승 마감, ‘그녀는 예뻤다’ 박서준·AOA 설현·레드벨벳 슬기…로엔 아이유 컴백 임박·CJ E&M ‘슈퍼스타K7’·‘신서유기’ 불구 하락</p>
							<p>코스피와 코스닥 지수가 소폭 상승 마감한 가운데 엔터주 역시 기지개를 켰다.</p>
							<p>코스피는 1일 전 거래일 대비 16.51포인트(0.84%) 상승한 1979.32로 마감했다. 코스닥 역시 같은 기간 6.31포인트(0.93%) 오른 684.79에 장을 마쳤다.</p>
							<p><a href="">난 링크다</a></p>

							<h2>list</h2>
							<ul>
								<li>안녕하세요
									<ul>
										<li>안녕하세요</li>
										<li>안녕하세요</li>
										<li>안녕하세요</li>
									</ul>
								</li>
								<li>안녕하세요</li>
								<li>안녕하세요</li>
							</ul>

							<ol>
								<li>안녕하세요</li>
								<li>안녕하세요</li>
								<li>안녕하세요</li>
							</ol>

							<h2>table</h2>
							<table border="1" cellpadding="1" cellspacing="1" style={{'width': '500px'}}>
								<thead>
								<tr>
									<th>타이틀</th>
									<th>타이틀</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td><a href="#">ㅇㅇㅇ</a></td>
									<td>ㅇㅇㅇ</td>
								</tr>
								<tr>
									<td>ㅇㅇㅇ</td>
									<td>ㅇㅇㅇ</td>
								</tr>
								<tr>
									<td>ㅇㅇ</td>
									<td>ㅇㅇㅇ</td>
								</tr>
								</tbody>
							</table>

							<h2>blockquote</h2>
							<blockquote>blockquote 영역</blockquote>

							<h2>pre</h2>
							<pre>pre 요소 test</pre>

							<h2>image</h2>
							<img src="http://www.mediaus.co.kr/news/photo/201012/15458_28375_5451.jpg" />
							<img src="http://www.mediaus.co.kr/news/photo/201012/15458_28375_5451.jpg" />
							<h3>iframe</h3>
							<iframe width="560" height="315" src="https://www.youtube.com/embed/JAAo30-86QA" frameborder="0" allowfullscreen=""></iframe>
						</div>
					</div>
				</div>

				<div className="read_footer">
					<div className="bd_file_list">
						<a href="#" className="bd_btn_file"><i className="xi-clip"></i><span className="xe-sr-only">파일 첨부 리스트</span> <strong className="bd_file_num">4</strong></a>
						<ul>
							<li><a href="#"><i className="xi-download"></i> 아이유 사진.jpg</a> <span className="bd_file_size">(5Mb)</span></li>
							<li><a href="#"><i className="xi-download"></i> 아이유 사진.jpg</a> <span className="bd_file_size">(5Mb)</span></li>
							<li><a href="#"><i className="xi-download"></i> 아이유 사진.jpg</a> <span className="bd_file_size">(5Mb)</span></li>
						</ul>
					</div>
					<div className="bd_function">
						<div className="bd_function_l">
							<a href="#" className="bd_ico bd_like"><i className="xi-heart"></i><span className="xe-sr-only">좋아요</span></a> <a href="#" className="bd_like_num">54</a>
							<a href="#" className="bd_ico bd_favorite"><i className="xi-star"></i><span className="xe-sr-only">즐겨찾기</span></a>
							<div className="bd_share_area">
								<a href="#" className="bd_ico bd_share"><i className="xi-external-link"></i><span className="xe-sr-only">공유</span></a>
								<div className="ly_popup">
									<ul>
										<li><a href="#"><i className="xi-facebook"></i> 페이스북</a></li>
										<li><a href="#"><i className="xi-twitter"></i> 트위터</a></li>
										<li><a href="#"><i className="xi-link"></i> 고유주소</a></li>
									</ul>
								</div>
							</div>
						</div>
						<div className="bd_function_r">
							<a href="#" className="bd_ico bd_modify"><i className="xi-eraser"></i><span className="xe-sr-only">수정</span></a>
							<a href="#" className="bd_ico bd_delete"><i className="xi-trash"></i><span className="xe-sr-only">삭제</span></a>
							<div className="bd_more_area">
								<a href="#" className="bd_ico bd_more_view"><i className="xi-ellipsis-h"></i><span className="xe-sr-only">더보기</span></a>
								<div className="ly_popup">
									<ul>
										<li><a href="#">신고</a></li>
										<li><a href="#">스패머관리</a></li>
										<li><a href="#">휴지통</a></li>
										<li><a href="#">등등</a></li>
									</ul>
								</div>
							</div>
						</div>
						<div className="bd_like_more">
							<ul>
								<li><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>
								<li><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>
								<li><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>
								<li><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>
								<li><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>

								<li className="on"><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>
								<li><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>
								<li><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>
								<li><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>
								<li><img src="sample/@iu.jpg" alt="아이유" title="아이유" /></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		);
	}
}

export default DetailView;