import { connect } from 'react-redux';
import { fetchViewSuccess } from './../actions/boardViewAction';
import DetailView from './../components/detail/DetailView';

const mapStateToProps = (state, ownProps) => {
	const id = state.routing.locationBeforeTransitions.pathname.split('/')[2];

	return {
		view: state.view,
		id
	};
}

const mapDispatchToProps = (dispatch) => {
	let viewApi = Common.get('apis').view;

	return {
		fetchDetailView: (id) => {
			XE.ajax({
				url: viewApi.replace('[id]', id),
				dataType: 'json',
				data: {},
				success: function(res) {
					dispatch(fetchViewSuccess(res));
				},
			});
		},
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(DetailView);