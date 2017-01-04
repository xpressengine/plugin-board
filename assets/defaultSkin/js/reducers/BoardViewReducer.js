import {
	FETCH_VIEW_SUCCESS, FETCH_VIEW_FAILURE
} from '../actions/boardViewAction';

const INITIAL_STATE = {
	item: {},
	links: {},
	showCategoryItem: '',
	visible: false
};

export default function(state = INITIAL_STATE, action) {
	let error;

	switch(action.type) {
		case FETCH_VIEW_SUCCESS:
			return Object.assign({}, state, action.payload);

		default:
			return state;
	}
}
