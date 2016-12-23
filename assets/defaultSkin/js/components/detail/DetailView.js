import React, { PropTypes } from 'react';
import { browserHistory, Router, Route, Link } from 'react-router'
import { timeAgo } from '../../utils';

class DetailView extends React.Component {

	static propTypes = {
		id: PropTypes.number.isRequired,
		view: PropTypes.object
	};

	componentWillMount() {
		this.props.fetchDetailView(this.props.id);
	}

	constructor(props, context) {
		super();
	}

	render() {

		console.log(this.props);

		let item = this.props.view.item;

		return (
			<div className="board_read">
				<div className="read_header">
					<span className="category">{item.category}</span>
					<h1><a href="#">{item.title}</a></h1>
					<div className="more_info">

						<a href="#" className="mb_autohr">{item.writer}</a>
						<span className="mb_time"><i className="xi-time"></i> {timeAgo(item.createdAt)}</span>
						<span className="mb_readnum"><i className="xi-eye"></i> {item.readCount}</span>
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
						<div className="__xe_contents_compiler" id="xe-editor-content" dangerouslySetInnerHTML={{__html: item.content}}></div>
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