import { connect } from 'react-redux';
import { fetchBoardIndex, CHECK_ALL, UNCHECK_ALL } from './../actions/boardListAction';
import BoardList from './../components/list/BoardList';

const mapStateToProps = (state) => {
	return {
		boardList: state.list.index.boardList,
		categories: state.list.index.categories,
		loading: state.list.index.loading,
		error: state.list.index.error,
		checkedAll: state.list.checkedAll,
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardIndex: () => {
			dispatch(fetchBoardIndex());
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