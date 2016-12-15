import React from 'react';
import ReactDOM from 'react-dom';

class BoardFooter extends React.Component {
	render() {
		return (
			<div className="board_footer">
				<div className="bd_paginate xe-hidden-xs">
					<span className="btn_pg btn_prev">
							<i className="xi-angle-left"><span className="xe-sr-only">이전</span></i>
					</span>
					<a href="#">1</a>
					<strong>2</strong>
					<a href="#">3</a>
					<a href="#">4</a>
					<a href="#">5</a>
					<a href="#">6</a>
					<a href="#">7</a>
					<a href="#">8</a>
					<span className="more_page">...</span>
					<a href="#">100</a>
					<a href="#" className="btn_pg btn_next">
						<i className="xi-angle-right"><span className="xe-sr-only">다음</span></i>
					</a>
				</div>

				<div className="bd_paginate v2 xe-visible-xs">

					<span className="btn_pg btn_prev">
							<i className="xi-angle-left"><span className="xe-sr-only">이전</span></i>
					</span>
					<span className="pg_box">
							<strong>1</strong> / <span>100</span>
					</span>
					<a href="#" className="btn_pg btn_next">
						<i className="xi-angle-right"><span className="xe-sr-only">다음</span></i>
					</a>
				</div>

			</div>
		);
	}
}

export default BoardFooter;