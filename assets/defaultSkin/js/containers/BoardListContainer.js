import { connect } from 'react-redux';
import { fetchBoardIndexSuccess, CHECK_ALL, UNCHECK_ALL, CHECK_ROW, UNCHECK_ROW } from './../actions/boardAction';
import BoardList from './../components/list/BoardList';

const mapStateToProps = (state) => {
	return {
		boardList: state.board.index.boardList,
		categories: state.board.index.categories,
		loading: state.board.index.loading,
		error: state.board.index.error,
		checkedAll: state.board.checkedAll,
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardIndex: () => {
			XE.ajax({
				url: Common.get('apis').index,
				type: 'get',
				dataType: 'json',
				data: {},
				success: function(res) {
					dispatch(fetchBoardIndexSuccess(res));
				},
			});
		},
		handleCheckAll: () => {
			dispatch({
				type: CHECK_ALL,
			});
		},
		handleUnCheckAll: () => {
			dispatch({
				type: UNCHECK_ALL,
			});
		},
		
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(BoardList);