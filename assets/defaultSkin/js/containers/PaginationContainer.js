import React, { Component } from 'react';
import { connect } from 'react-redux';
import { fetchBoardIndexSuccess } from './../actions/boardAction';
import Pagination from './../components/list/Pagination';

const mapStateToProps = (state) => {
	return {
		paginate: state.board.index.paginate
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardIndex: ({
			currentPage,
			perPage,
		}) => {
			XE.ajax({
				url: Common.get('apis').index,
				dataType: 'json',
				data: {
					current_page: currentPage,
					per_page: perPage
				},
				success: function(res) {
					dispatch(fetchBoardIndexSuccess(res));
				},
			});
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(Pagination);