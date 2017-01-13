import { connect } from 'react-redux';
import { reduxForm, change } from 'redux-form';
import { fetchBoardIndex } from './../actions/boardListAction';
import WriteForm from './../components/write/WriteForm';

const form = 'writeForm';
const fields = ['title', 'content', 'slug', 'categoryItemId'];
const formConfig = {
	form,
	fields
};
const mapStateToProps = (state) => {
	return {
		categories: state.list.index.categories,
		categoryItemId: state.write.categoryItemId
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardIndex: () => {
			dispatch(fetchBoardIndex());
		},
		handleSelect: (categoryItemId) => {
			dispatch(change(form, 'categoryItemId', categoryItemId));
		},
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(reduxForm(formConfig)(WriteForm));