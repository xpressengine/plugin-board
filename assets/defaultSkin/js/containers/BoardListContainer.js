import { connect } from 'react-redux';
import { fetchBoardListSuccess } from './../actions/boardAction';
import BoardList from './../components/list/BoardList';

const mapStateToProps = (state) => {
	return {
		list: state.board.list
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardList: () => {
			XE.ajax({
				url: Common.get('apis').list,
				dataType: 'json',
				data: {},
				success: function(res) {
					dispatch(fetchBoardListSuccess(res));
				},
			});
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(BoardList);