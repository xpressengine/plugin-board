import React, { Component } from 'react';
import { connect } from 'react-redux';
// import { fetchBoard } from './../actions/boardAction';
import BoardListHeader from './../components/list/BoardListHeader';

const mapStateToProps = (state) => {
	return {
		categories: state.board.index.categories
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		changeCategory: () => {

		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(BoardListHeader);