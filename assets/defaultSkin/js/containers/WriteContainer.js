import { connect } from 'react-redux';
import { fetchBoardIndex } from './../actions/boardListAction';
import { createBoardContents, addSuccess } from './../actions/boardWriteAction';
import WriteForm from './../components/write/WriteForm';

const mapStateToProps = (state) => {
	return {
		categories: state.list.index.categories
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardIndex: () => {
			dispatch(fetchBoardIndex());
		},
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(WriteForm);