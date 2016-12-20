import { connect } from 'react-redux';
import { fetchDetailSuccess } from './../actions/boardAction';
import DetailView from './../components/detail/DetailView';

const mapStateToProps = (state) => {
	return {
		 detail: state.board.deatil
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchDetailView: (id) => {
			XE.ajax({
				url: Common.get('apis').detail + `/${id}`,
				dataType: 'json',
				data: {},
				success: function(res) {
					dispatch(fetchDetailSuccess(res));
				},
			});
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(DetailView);