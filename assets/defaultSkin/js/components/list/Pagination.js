import React from 'react';
import ReactDOM from 'react-dom';

class Pagination extends React.Component {

	constructor(props) {
		super(props);

		this.requestPrevBlock = ::this.requestPrevBlock;
		this.requestNextBlock = ::this.requestNextBlock;
	}

	getPaginationInfo() {
		/**
		 * currerntPage - 현재 페이지
		 * lastPage - 마지막 페이지
		 * perPageBlockCount - block당 보여질 page정보 갯수
		 * blockStartPage - block의 첫 페이지 number
		 * blockEndPage - block의 마지막 페이지 number
		 * currentBlockNum - 현재 block의 number
		 * lastBlockNum - 마지막 block의 number
		 * */
		let currentPage = this.props.paginate.currentPage;
		let lastPage = this.props.paginate.lastPage;
		let perPageBlockCount = this.props.paginate.perPageBlockCount;
		let blockStartPage = Math.ceil(currentPage / perPageBlockCount);
		let currentBlockNum = Math.floor(currentPage / perPageBlockCount) || 1;
		let lastBlockNum = Math.ceil(lastPage / perPageBlockCount) - 1;
		let blockEndPage;

		blockStartPage = (blockStartPage - 1) * perPageBlockCount + 1;
		blockEndPage = (blockStartPage + (perPageBlockCount - 1) > lastPage)? lastPage: blockStartPage + (perPageBlockCount - 1);

		return {
			currentPage,
			lastPage,
			perPageBlockCount,
			blockStartPage,
			blockEndPage,
			currentBlockNum,
			lastBlockNum
		}
	}

	requestPrevBlock() {
		let paginationInfo = this.getPaginationInfo();
		let blockStartPage = paginationInfo.blockStartPage;
		let prevPage = blockStartPage - 1;

		this.props.fetchBoardIndex({pageNum: prevPage});
	}

	requestNextBlock() {
		let paginationInfo = this.getPaginationInfo();
		let perPageBlockCount = paginationInfo.perPageBlockCount;
		let blockStartPage = paginationInfo.blockStartPage;
		let nextPage = blockStartPage + perPageBlockCount;

		this.props.fetchBoardIndex({pageNum: nextPage});
	}

	renderPrevPage() {
		let paginationInfo = this.getPaginationInfo();
		let currentBlockNum = paginationInfo.currentBlockNum;

		if(currentBlockNum > 1) {
			return (
				<a href="#" className="btn_pg btn_prev" onClick={this.requestPrevBlock}>
					<i className="xi-angle-left"><span className="xe-sr-only">이전</span></i>
				</a>
			)
		} else {
			return (
				<span className="btn_pg btn_prev">
						<i className="xi-angle-left"><span className="xe-sr-only">이전</span></i>
				</span>
			)
		}
	}

	renderNextPage() {
		let paginationInfo = this.getPaginationInfo();
		let currentBlockNum = paginationInfo.currentBlockNum;
		let lastBlockNum = paginationInfo.lastBlockNum;

		if(lastBlockNum > currentBlockNum) {
			return (
				<a href="#" className="btn_pg btn_next" onClick={this.requestNextBlock} >
					<i className="xi-angle-right"><span className="xe-sr-only">다음</span></i>
				</a>
			)
		} else {
			return (
				<span href="#" className="btn_pg btn_next">
					<i className="xi-angle-right"><span className="xe-sr-only">다음</span></i>
				</span>
			)
		}
	}

	fetchBoardIndex(pageNum) {
		this.props.fetchBoardIndex({pageNum});
	}

	render() {
		let paginationInfo = this.getPaginationInfo();
		let currentPage = paginationInfo.currentPage;
		let lastPage = paginationInfo.lastPage;
		let perPageBlockCount = paginationInfo.perPageBlockCount;

		if((currentPage === 0 || currentPage === 1 && currentPage === lastPage)) {
			return (
				<div className="board_footer"></div>
			);
		}

		return (
			<div className="board_footer">
				<div className="bd_paginate xe-hidden-xs">
					{ this.renderPrevPage() }
					{
						(() => {
							let pages = [];
							let blockStartPage = paginationInfo.blockStartPage;
							let blockEndPage = paginationInfo.blockEndPage;

							for(var i = blockStartPage, max = blockEndPage; i <= max; i += 1) {
								if(currentPage === i) {
									pages.push(<strong>{i}</strong>)
								} else {
									pages.push(<a href="#" onClick={ this.fetchBoardIndex.bind(this, i) }>{i}</a>);
								}

								if(lastPage === i) {
									break;
								}
							}

							return pages;
						})()
					}
					{ this.renderNextPage() }
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

export default Pagination;