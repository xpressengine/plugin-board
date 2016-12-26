import { connect } from 'react-redux';
import { addSuccess } from './../actions/boardListAction';
import EditForm from './../components/write/EditForm';

const mapStateToProps = (state) => {
	return {

	};
}

const mapDispatchToProps = (dispatch) => {
	return {
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