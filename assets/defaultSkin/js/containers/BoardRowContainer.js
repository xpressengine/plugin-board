import { connect } from 'react-redux';
import { CHECK_ROW, UNCHECK_ROW } from './../actions/boardAction';
import BoardRow from './../components/list/BoardRow';

const mapStateToProps = (state) => {
	return {
		checkedMap: state.board.checkedMap,
		categories: state.board.index.categories
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		handleCheck: (id) => {
			dispatch({
				id,
				type: CHECK_ROW,
			});
		},
		handleUnCheck: (id) => {
			dispatch({
				id,
				type: UNCHECK_ROW,
			});
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(BoardRow);