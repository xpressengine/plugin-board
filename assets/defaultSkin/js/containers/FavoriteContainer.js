import { connect } from 'react-redux';
import { fetchBoardListSuccess } from './../actions/boardAction';
import Favorite from './../components/list/Favorite';

const mapStateToProps = (state) => {
	return {
		list: state.board.list
	};
}

const mapDispatchToProps = (dispatch) => {
	return {
		setFavorite: (info) => {

			let { id, isFavorite } = info;

			console.log('info :: ', info);

			return;

			XE.ajax({
				url: Common.get('apis').favorite,
				dataType: 'json',
				data: {},
				success: function(res) {
					dispatch(fetchBoardListSuccess(res));
				},
			});
		}
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(Favorite);