import {
	FETCH_VIEW, FETCH_VIEW_SUCCESS, FETCH_VIEW_FAILURE,
} from '../actions/boardViewAction';

const INITIAL_STATE = {
	categories: [],
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
			return { ...state, loading: true };

		case FETCH_VIEW_SUCCESS:
			return { ...state, ...action.payload , loading: false, error: null}//Object.assign({}, state, action.payload);

		case FETCH_VIEW_FAILURE:
			return { ...state, ...action.payload , loading: false, error: action.payload}//Object.assign({}, state, action.payload);

		default:
			return state;
	}
}
