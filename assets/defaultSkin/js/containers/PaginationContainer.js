import React, { Component } from 'react';
import { connect } from 'react-redux';
import { fetchBoardIndexSuccess } from './../actions/boardListAction';
import Pagination from './../components/list/Pagination';

const mapStateToProps = (state) => {
	return {
		paginate: state.list.index.paginate
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardIndex: ({
			pageNum
		}) => {
			XE.ajax({
				url: Common.get('apis').index,
				type: 'get',
				dataType: 'json',
				data: {
					page: pageNum
				},
				success: function(res) {
					dispatch(fetchBoardIndexSuccess(res));
				},
			});
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(Pagination);