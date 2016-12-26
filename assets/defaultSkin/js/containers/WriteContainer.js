import { connect } from 'react-redux';
import { fetchBoardIndexSuccess } from './../actions/boardListAction';
import { addSuccess } from './../actions/boardWriteAction';
import WriteForm from './../components/write/WriteForm';

const mapStateToProps = (state) => {
	return {
		categories: state.list.index.categories
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchBoardIndex: () => {
			XE.ajax({
				url: Common.get('apis').index,
				type: 'get',
				dataType: 'json',
				data: {},
				success: function(res) {
					dispatch(fetchBoardIndexSuccess(res));
				},
			});
		},
		addContents: (data) => {
			XE.ajax({
				url: Common.get('apis').create,
				type: 'post',
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