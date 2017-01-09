import { connect } from 'react-redux';
import { FETCH_VIEW, fetchView } from './../actions/boardViewAction';
import DetailView from './../components/detail/DetailView';

const mapStateToProps = (state, ownProps) => {
	const id = state.routing.locationBeforeTransitions.pathname.split('/')[2];

	return {
		view: state.view,
		id,
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		fetchDetailView: (id) => {
			dispatch(fetchView(id));
		},
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(DetailView);