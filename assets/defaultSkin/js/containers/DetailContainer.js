import { connect } from 'react-redux';
import { fetchCategories, fetchView } from './../actions/boardViewAction';
import DetailView from './../components/detail/DetailView';

const mapStateToProps = (state, ownProps) => {
	return {
		view: state.view,
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		// fetchCategories: () => {
		// 	dispatch(fetchCategories());
		// },
		fetchDetailView: (id) => {
			dispatch(fetchView(id));
		},
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(DetailView);