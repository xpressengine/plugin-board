import React from 'react';

import BoardRowContainer from './../../containers/BoardRowContainer';
import BoardListHeaderContainer from './../../containers/BoardListHeaderContainer';
import PaginationContainer from './../../containers/PaginationContainer';
import Spinner from './../Spinner';

export default class BoardList extends React.Component {

	static propTypes = {
		boardList: React.PropTypes.object,
		categories: React.PropTypes.array
	};

	constructor(props) {
		super(props);

		this.onChangeCheckAll = this.onChangeCheckAll.bind(this);
	}

	componentWillMount() {
		console.log('BoardList componentWillMount:fn');
		this.props.fetchBoardIndex();
	}

	onChangeCheckAll(e) {
		let target = e.target.checked;

		if(target) {
			this.props.handleCheckAll();
		} else {
			this.props.handleUnCheckAll();
		}
	}

	render() {

		console.log('this.props.loading', this.props.loading);

		if(this.props.loading) {
			return <Spinner />
		}

		return (
			<div>
				<BoardListHeaderContainer />

				<div className="board_list">
					<table>
						<thead className="xe-hidden-xs">
						<tr>
							{
								(() => {
									if(Common.get('user').isManager) {
										return (
											<th scope="col">
												<label className="xe-label">
													<input type="checkbox" onChange={ this.onChangeCheckAll } checked={this.props.checkedAll} />
													<span className="xe-input-helper"></span>
													<span className="xe-label-text xe-sr-only">전체 선택</span>
												</label>
											</th>
										);
									}
								})()
							}
							<th scope="col" className="favorite"><span><a href="#" title="즐겨찾기 전부 체크"><i className="xi-star-o"></i><span className="xe-sr-only">즐겨찾기 전부 체크</span></a></span></th>
							{
								(() => {
									if(this.props.categories.length > 0) {
										return (<th scope="col"><span>카테고리</span></th>);
									}
								})()
							}
							<th scope="col" className="title"><span>제목</span></th>
							<th scope="col"><span>글쓴이</span></th>
							<th scope="col"><span><a href="#">조회수</a></span></th>
							<th scope="col"><span><a href="#">날짜</a></span></th>
						</tr>
						</thead>
						<tbody>
						{
							this.props.boardList.map((row, i) => {
								return (
										<BoardRowContainer {...row} />
									)
							})
						}
						</tbody>
					</table>
				</div>

				<PaginationContainer />
			</div>
		);
	}
};
