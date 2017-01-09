import {
	FETCH_VIEW, FETCH_VIEW_SUCCESS, FETCH_VIEW_FAILURE
} from '../actions/boardViewAction';

const INITIAL_STATE = {
	item: {},
	links: {},
	showCategoryItem: '',
	visible: false,
	loading: false,
	error: null
};

export default function(state = INITIAL_STATE, action) {
	let error;

	switch(action.type) {
		case FETCH_VIEW:
			return { ...state, loading: true};

		case FETCH_VIEW_SUCCESS:
			return Object.assign({}, state, action.payload);

		default:
			return state;
	}
}
