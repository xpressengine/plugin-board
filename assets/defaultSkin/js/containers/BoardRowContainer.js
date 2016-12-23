import { connect } from 'react-redux';
import { CHECK_ROW, UNCHECK_ROW } from './../actions/boardListAction';
import BoardRow from './../components/list/BoardRow';

const mapStateToProps = (state) => {
	return {
		checkedMap: state.list.checkedMap,
		categories: state.list.index.categories
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