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
	const id = state.routing.locationBeforeTransitions.pathname.split('/')[2];

	return {
		edit: state.edit,
		id
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
			XE.ajax({
				url: Common.get('apis').update,
				type: 'put',
				dataType: 'json',
				data: data,
				success: function(res) {
					dispatch(addSuccess(res));
				},
			});
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(reduxForm(formConfig)(EditForm));