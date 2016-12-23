import _ from 'lodash';
import {
	FETCH_BOARD_SUCCESS, FETCH_BOARD_FAILURE
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
		case FETCH_BOARD_SUCCESS:
			return Object.assign({}, state, action.payload)//{ ...state, {action.payload}}

		default:
			return state;
	}
}
