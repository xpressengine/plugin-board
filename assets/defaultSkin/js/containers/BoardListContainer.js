import { connect } from 'react-redux';
import { fetchBoardIndexSuccess, CHECK_ALL, UNCHECK_ALL, CHECK_ROW, UNCHECK_ROW } from './../actions/boardListAction';
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