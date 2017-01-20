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
		initialValues: state.edit.item,
		item: state.edit.item,
		categories: state.edit.categories,
		loading: state.edit.loading,
		err: state.edit.error,
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
		changeFormField: ({ field, value }) => {
			console.log(field, value);
			dispatch(change(form, field, value));
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(reduxForm(formConfig)(EditForm));