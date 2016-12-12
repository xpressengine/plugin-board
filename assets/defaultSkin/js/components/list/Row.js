import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';

class Row extends React.Component {

	static propTypes = {
		id: PropTypes.number.isRequired,
	};

	constructor(props) {
		super(props);
	}

	render() {
		//{'#/detail/' + this.props.id}
		return (
			<tr>
				{
					(() => {
						if(Common.get('user').isManager) {
							return (
								<td className="check">
									<label className="xe-label">
										<input type="checkbox" />
										<span className="xe-input-helper"></span>
										<span className="xe-label-text xe-sr-only">선택</span>
									</label>
								</td>
							);
						}
					})()
				}
				<td className="favorite xe-hidden-xs"><a href="#" title="즐겨찾기 체크"><i className="xi-star-o"></i><span className="xe-sr-only">즐겨찾기 체크</span></a></td>
				<td className="category xe-hidden-xs">Q&amp;A</td>
				<td className="title">
					<span className="xe-badge xe-primary-outline xe-visible-xs-inline-block">Q&amp;A</span>
					<span className="bd_ico_lock"><i className="xi-lock"></i><span className="xe-sr-only">secret</span></span>
					<a href="#" className="title_text">안녕하세요. 안녕하세요. 안녕하세요. 안녕하세요. 안녕하세요.</a>
					<a href="#" className="reply_num xe-hidden-xs" title="Replies">9</a>
					<span className="bd_ico_file"><i className="xi-clip"></i><span className="xe-sr-only">file</span></span>
					<span className="bd_ico_new"><i className="xi-new"></i><span className="xe-sr-only">new</span></span>
					<div className="more_info xe-visible-xs">
						<a href="" className="mb_autohr">XE</a>
						<span className="mb_time"><i className="xi-time"></i> 15시간 전</span>
						<span className="mb_read_num"><i className="xi-eye"></i> 78</span>
						<a href="#" className="mb_reply_num"><i className="xi-comment"></i> 9</a>
					</div>
				</td>
				<td className="author xe-hidden-xs"><a href="#">XE</a></td>
				<td className="read_num xe-hidden-xs">78</td>
				<td className="time xe-hidden-xs">15시간 전</td>
			</tr>
		);
	}
};

export default Row;
