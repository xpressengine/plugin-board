import { connect } from 'react-redux';
import { reduxForm, change } from 'redux-form';
import { FETCH_VIEW } from './../actions/boardViewAction';
import EditForm from './../components/write/EditForm';

const form = 'editForm';
const fields = ['title', 'content', 'slug', 'categoryItemId'];
const formConfig = {
	form,
	fields
};

const mapStateToProps = (state) => {
	return {
		item: state.edit.item,
		categories: state.edit.categories,
		loading: state.edit.loading,
		error: state.edit.error
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchView: (id) => {
			dispatch({
				type: FETCH_VIEW,
				id
			})
		},
		updateContents: (data) => {

		},
		handleSelect: (categoryItemId) => {
			dispatch(change(form, 'categoryItemId', categoryItemId));
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(reduxForm(formConfig)(EditForm));