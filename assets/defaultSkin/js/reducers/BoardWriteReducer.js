import {
	ADD_CONTENTS, ADD_CONTENTS_SUCCESS, ADD_CONTENTS_FAILURE, DETAIL_RESET
} from '../actions/boardWriteAction';

const INITIAL_STATE = {
	error: null,
	loading: false,
	item: null,
};

export default function(state = INITIAL_STATE, action) {
	let error;

	switch(action.type) {
		case ADD_CONTENTS:
			return { ...state, loading: true,  }

		case ADD_CONTENTS_SUCCESS:
			return { ...state, loading: false, error: null, item: action.payload.item };

		case ADD_CONTENTS_FAILURE:
			return { ...state, loading: false, error: action.error }

		case DETAIL_RESET:
			return { ...state, loading: false, item: null, error: null }

		default:
			return state;
	}
}
