import React from 'react';
import ReactDOM from 'react-dom';

class Pagination extends React.Component {

	constructor(props) {
		super(props);

		this.requestPrevBlock = this.requestPrevBlock.bind(this);
		this.requestNextBlock = this.requestNextBlock.bind(this);
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
		let blockStartPage = ((parseInt(currentPage / perPageBlockCount, 10) * perPageBlockCount) + 1);
		let blockEndPage = blockStartPage + (perPageBlockCount - 1);
		let currentBlockNum = parseInt(currentPage / perPageBlockCount, 10) + 1;
		let lastBlockNum = parseInt(lastPage / perPageBlockCount, 10) + 1;

		blockEndPage = (blockEndPage > lastPage)? lastPage: blockEndPage;

		return {
			currentPage: currentPage,
			lastPage: lastPage,
			perPageBlockCount: perPageBlockCount,
			blockStartPage: blockStartPage,
			blockEndPage: blockEndPage,
			currentBlockNum: currentBlockNum,
			lastBlockNum: lastBlockNum
		}
	}

	requestPrevBlock() {
		let paginationInfo = this.getPaginationInfo();
		let blockStartPage = paginationInfo.blockStartPage;
		let prevPage = blockStartPage - 1;


	}

	requestNextBlock() {
		let paginationInfo = this.getPaginationInfo();
		let perPageBlockCount = paginationInfo.perPageBlockCount;
		let blockStartPage = paginationInfo.blockStartPage;
		let nextPage = blockStartPage + perPageBlockCount;


	}

	renderPrevPage() {
		let paginationInfo = this.getPaginationInfo();
		let currentBlockNum = paginationInfo.currentBlockNum;

		if(currentBlockNum > 1) {
			return (
				<a href="#" className="btn_pg btn_prev">
					<i className="xi-angle-left"><span className="xe-sr-only" onClick={this.requestPrevBlock}>이전</span></i>
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
				<a href="#" className="btn_pg btn_next">
					<i className="xi-angle-right"><span className="xe-sr-only" onClick={this.requestNextBlock} >다음</span></i>
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

	requestPage(pageNum) {
		console.log(pageNum);
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
							let blockStartPage = (parseInt(currentPage / 10, 10) + 1);

							for(var i = blockStartPage, max = perPageBlockCount; i <= max; i += 1) {
								if(currentPage === i) {
									pages.push(<strong>{i}</strong>)
								} else {
									pages.push(<a href="#" onClick={ this.requestPage.bind(this, i) }>{i}</a>);
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