import React, { PropTypes } from 'react';
import { Link } from 'react-router';
import _ from 'lodash';

import Favorite from './../../containers/FavoriteContainer';
import { timeAgo, isNew } from 'utils';

class BoardRow extends React.Component {

	static propTypes = {
		id: React.PropTypes.string.isRequired
	};

	constructor(props) {
		super(props);

		this.handleCheckRow = ::this.handleCheckRow;
	}

	handleCheckRow(e) {
		let target = e.target;
		let checked = target.checked;

		if(checked) {
			this.props.handleCheck(this.props.id);
		} else {
			this.props.handleUnCheck(this.props.id);
		}
	}

	render() {
		const favoriteConfig = {
			id: this.props.id,
			favorite: this.props.favorite,
		};

		const categories = this.props.categories;
		const category = this.props.category;
		const categoryName = category? _.find(categories, o => ( o.value == category.itemId )).text : '없음';

		return (
			<tr>
				{
					(() => {
						if(Common.get('user').isManager) {

							return (
								<td className="check">
									<label className="xe-label">
										<input type="checkbox" onChange={ this.handleCheckRow } checked={this.props.checkedMap[this.props.id]} />
										<span className="xe-input-helper"></span>
										<span className="xe-label-text xe-sr-only">선택</span>
									</label>
								</td>
							);
						}
					})()
				}
				<Favorite {...favoriteConfig} />
				{
					(() => {
						if(this.props.categories.length > 0) {
							return (
								<td className="category xe-hidden-xs">{ categoryName }</td>
							)
						}
					})()
				}
				<td className="title">
					{
						(() => {
							if(this.props.categories.length > 0) {
								return (
									<span className="xe-badge xe-primary-outline xe-visible-xs-inline-block">{ categoryName }</span>
								)
							}
						})()
					}
					{
						(() => {
							//TODO secret 노출 조건 확인
							if(this.props.secret) {
								return (<span className="bd_ico_lock"><i className="xi-lock"></i><span className="xe-sr-only">secret</span></span>);
							}
						})()
					}
					<Link to={`/detail/${this.props.id}`} className="title_text">{ this.props.title }</Link>
					<a href="#" className="reply_num xe-hidden-xs" title="Replies">{ this.props.commentCount > 0 ? this.props.commentCount : '' }</a>
					{
						(() => {
							if(this.props.fileCount > 0) {
								return (
									<span className="bd_ico_file"><i className="xi-clip"></i><span className="xe-sr-only">file</span></span>
								)
							}
						})()
					}
					{
						(() => {
							if(isNew(this.props.createdAt)) {
								return (
									<span className="bd_ico_new"><i className="xi-new"></i><span className="xe-sr-only">new</span></span>
								)
							}
						})()
					}
					<div className="more_info xe-visible-xs">
						<a href="" className="mb_autohr">{ this.props.user.displayName }</a>
						<span className="mb_time"><i className="xi-time"></i> { timeAgo(this.props.createdAt) }</span>
						<span className="mb_read_num"><i className="xi-eye"></i> { this.props.readCount }</span>
						<a href="#" className="mb_reply_num"><i className="xi-comment"></i> { this.props.commentCount > 0 ? this.props.commentCount : '' }</a>
					</div>
				</td>
				<td className="author xe-hidden-xs"><a href="#">{ this.props.user.displayName }</a></td>
				<td className="read_num xe-hidden-xs">{ this.props.readCount }</td>
				<td className="time xe-hidden-xs">{ timeAgo(this.props.createdAt) }</td>
			</tr>
		);
	}
};

export default BoardRow;
