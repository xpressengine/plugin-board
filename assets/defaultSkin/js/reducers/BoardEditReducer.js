import {
	EDIT_BOARD, EDIT_BOARD_SUCCESS, EDIT_BOARD_FAILURE
} from '../actions/boardEditAction';

import {
	FETCH_VIEW
} from '../actions/boardViewAction';

const INITIAL_STATE = {
	view: {},
	error: null,
	loading: false,
};

export default function(state = INITIAL_STATE, action) {
	let error;

	switch(action.type) {
		case FETCH_VIEW:
			return {...state, view: action.payload}

		case EDIT_BOARD:
			return { ...state };

		case EDIT_BOARD_SUCCESS:
			return Object.assign({}, state, action.payload);

		default:
			return state;
	}
}
