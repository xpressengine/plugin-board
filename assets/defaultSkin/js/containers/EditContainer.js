import { connect } from 'react-redux';
import { FETCH_VIEW } from './../actions/boardViewAction';
import EditForm from './../components/write/EditForm';

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
				url: Common.get('apis').edit,
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

export default connect(mapStateToProps, mapDispatchToProps)(EditForm);