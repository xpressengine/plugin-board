import { connect } from 'react-redux';
import { fetchBoardIndexSuccess } from './../actions/boardAction';
import BoardList from './../components/list/BoardList';

const mapStateToProps = (state) => {
	return {
		boardList: state.board.index.boardList,
		loading: state.board.index.loading,
		error: state.board.index.error
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardIndex: () => {
			XE.ajax({
				url: Common.get('apis').index,
				dataType: 'json',
				data: {},
				success: function(res) {
					dispatch(fetchBoardIndexSuccess(res));
				},
			});
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(BoardList);