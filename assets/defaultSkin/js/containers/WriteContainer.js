import { connect } from 'react-redux';
import { reduxForm, change } from 'redux-form';
import { fetchBoardIndex } from './../actions/boardListAction';
import { resetWriteForm } from './../actions/boardWriteAction';
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
		categoryItemId: state.write.categoryItemId,
		item: state.write.item,
		err: state.write.error,
		loading: state.write.loading
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		resetComponent: () => {
			dispatch(resetWriteForm());
		},
		fetchBoardIndex: () => {
			dispatch(fetchBoardIndex());
		},
		handleSelect: (categoryItemId) => {
			dispatch(change(form, 'categoryItemId', categoryItemId));
		},
		changeFormField: ({ field, value }) => {
			dispatch(change(form, field, value));
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(reduxForm(formConfig)(WriteForm));