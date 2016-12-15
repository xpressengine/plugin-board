import { connect } from 'react-redux';
import { addSuccess } from './../actions/boardAction';
import WriteForm from './../components/write/WriteForm';

const mapStateToProps = (state) => {
	return {
		// list: state.board.list
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		addPost: (data) => {
			XE.ajax({
				url: Common.get('apis').create,
				dataType: 'json',
				data: data,
				success: function(res) {
					dispatch(addSuccess(res));
				},
			});
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(WriteForm);