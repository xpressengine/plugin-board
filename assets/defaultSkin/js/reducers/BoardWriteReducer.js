import {
	ADD_CONTENTS, ADD_CONTENTS_SUCCESS, ADD_CONTENTS_FAILURE
} from '../actions/boardWriteAction';

const INITIAL_STATE = {
	error: null,
	loading: false,
};

export default function(state = INITIAL_STATE, action) {
	let error;

	switch(action.type) {
		case ADD_CONTENTS:
			return { ...state, loading: true }

		case ADD_CONTENTS_SUCCESS:
			return { ...state, loading: false, error: null };

		case ADD_CONTENTS_FAILURE:
			return { ...state, loading: false, error: action.error }

		default:
			return state;
	}
}
