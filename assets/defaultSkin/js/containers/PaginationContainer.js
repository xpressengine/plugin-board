import React, { Component } from 'react';
import { connect } from 'react-redux';
import { fetchBoardIndex } from './../actions/boardListAction';
import Pagination from './../components/list/Pagination';

const mapStateToProps = (state) => {
	return {
		paginate: state.list.index.paginate
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardIndex: (queryJson) => {
			let json = {}
			json.page = queryJson.pageNum;

			dispatch(fetchBoardIndex(json));
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(Pagination);