import React from 'react';
import ReactDOM from 'react-dom';
import Row from './Row';

import Header from'./Header';
import Footer from'./Footer';

const listCount = [1,2,3,4,5];

export default class BoardList extends React.Component {

	constructor(props) {
		super(props);
	}

	componentDidMount() {
		XE.ajax(Common.get('apis').list, {
			success (res) {
				console.log('res', res);
				// this.props.onReciveList(res.paginate.data);
			}
		});
	}

	render() {
		return (
			<div>
				<Header />

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
													<input type="checkbox" onChange={this.onChangeCheckAll.bind(this)} />
													<span className="xe-input-helper"></span>
													<span className="xe-label-text xe-sr-only">전체 선택</span>
												</label>
											</th>
										);
									}
								})()
							}
							<th scope="col" className="favorite"><span><a href="#" title="즐겨찾기 전부 체크"><i className="xi-star-o"></i><span className="xe-sr-only">즐겨찾기 전부 체크</span></a></span></th>
							<th scope="col"><span>카테고리</span></th>
							<th scope="col" className="title"><span>제목</span></th>
							<th scope="col"><span>글쓴이</span></th>
							<th scope="col"><span><a href="#">조회수</a></span></th>
							<th scope="col"><span><a href="#">날짜</a></span></th>
						</tr>
						</thead>
						<tbody>
						{
							listCount.map((obj, i) => {
								console.log(obj, i);
								return (

									<Row id={obj} id={obj} />
								);
							})
						}
						</tbody>
					</table>
				</div>

				<Footer />
			</div>
		);
	}

	onChangeCheckAll(e) {
		let target = e.target;
	}
};
