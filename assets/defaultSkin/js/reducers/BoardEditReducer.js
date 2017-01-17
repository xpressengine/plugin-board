import {
	EDIT_BOARD, EDIT_BOARD_SUCCESS, EDIT_BOARD_FAILURE
} from '../actions/boardEditAction';

import {
	FETCH_VIEW, FETCH_VIEW_SUCCESS, FETCH_VIEW_FAILURE
} from '../actions/boardViewAction';

const INITIAL_STATE = {
	item: null,
	categories: [],
	error: null,
	loading: true,
};

export default function(state = INITIAL_STATE, action) {
	let error;

	switch(action.type) {
		case FETCH_VIEW:
			return { ...state, loading: true };

		case FETCH_VIEW_SUCCESS:
			return { ...state, categories: action.payload.categories, item: action.payload.item , loading: false, error: null}//Object.assign({}, state, action.payload);

		case FETCH_VIEW_FAILURE:
			return { ...state, ...action.payload , loading: false, error: action.payload}//Object.assign({}, state, action.payload);

		case EDIT_BOARD:
			return { ...state, loading: true, error: null };

		case EDIT_BOARD_SUCCESS:
			return { ...state, ...action.payload, loading: false, error: null }

		case EDIT_BOARD_FAILURE:
			return { ...state, ...action.payload, loading: false }

		default:
			return state;
	}
}
