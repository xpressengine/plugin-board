import {
	FETCH_VIEW, FETCH_VIEW_SUCCESS, FETCH_VIEW_FAILURE,
} from '../actions/boardViewAction';

const INITIAL_STATE = {
	categories: [],
	item: {},
	// links: {},
	// showCategoryItem: '',
	visible: false,
	loading: true,
	error: null
};

export default function(state = INITIAL_STATE, action) {
	let error;

	switch(action.type) {
		case FETCH_VIEW:
			return { ...state, loading: true, error: null };

		case FETCH_VIEW_SUCCESS:
			return { ...state, categories: action.payload.categories, item: action.payload.item , loading: false, error: null}//Object.assign({}, state, action.payload);

		case FETCH_VIEW_FAILURE:
			return { ...state, loading: false, error: action.payload}//Object.assign({}, state, action.payload);

		default:
			return state;
	}
}
