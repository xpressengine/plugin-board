import {
	FETCH_BOARD_INDEX_SUCCESS, FETCH_BOARD_INDEX_FAILURE,
	CHECK_ALL, UNCHECK_ALL, CHECK_ROW, UNCHECK_ROW,
	FETCH_BOARD_SUCCESS, FETCH_BOARD_FAILURE
	// FETCH_POST, FETCH_POST_SUCCESS,  FETCH_POST_FAILURE, RESET_ACTIVE_POST,
	// CREATE_POST, CREATE_POST_SUCCESS, CREATE_POST_FAILURE, RESET_NEW_POST,
	// DELETE_POST, DELETE_POST_SUCCESS, DELETE_POST_FAILURE, RESET_DELETED_POST,
	// VALIDATE_POST_FIELDS,VALIDATE_POST_FIELDS_SUCCESS, VALIDATE_POST_FIELDS_FAILURE, RESET_POST_FIELDS
} from '../actions/boardAction';

const INITIAL_STATE = {
	index: {
		boardList: [],
		categories: [],
		error: null,
		loading: false,
	},
	checkedMap: {},
	detail: {
		board: null, error: null, loading: false
	},
};

export default function(state = INITIAL_STATE, action) {
	let error;

	switch(action.type) {
		case FETCH_BOARD_INDEX_SUCCESS:// return list of posts and make loading = false

			var checkedMap = {};
			var boardList = action.payload.paginate.data;

			boardList.map((obj) => {
				checkedMap[obj.id] = false;
			});

			// checkedMap['6a8ab167-cc57-4a5c-bbeb-c89256eaaf80'] = true;

			return { ...state, index: {boardList: boardList, categories: action.categories, error:null, loading: false}, checkedMap: checkedMap };

		case CHECK_ALL:
			var checkedMap = {};
			var boardList = state.index.boardList;

			boardList.map((obj, i) => {
				checkedMap[obj.id] = true;
			});

			return { ...state, checkedMap: checkedMap};

		case UNCHECK_ALL:
			var checkedMap = {};
			var boardList = state.index.boardList;

			boardList.map((obj, i) => {
				checkedMap[obj.id] = false;
			});

			return { ...state, checkedMap: checkedMap};

		case CHECK_ROW:
			var checkedMap = state.checkedMap;

			checkedMap[action.id] = true;


			console.log(checkedMap);

			return { ...state, checkedMap: checkedMap};

		case UNCHECK_ROW:
			var checkedMap = state.checkedMap;

			checkedMap[action.id] = false;

			console.log(checkedMap);

			return { ...state, checkedMap: checkedMap};

		default:
			return state;
		// 	return { ...state, postsList: {posts: [], error: error, loading: false} };
		// case RESET_POSTS:// reset postList to initial state
		// 	return { ...state, postsList: {posts: [], error:null, loading: false} };
		//
		// case FETCH_POST:
		// 	return { ...state, activePost:{...state.activePost, loading: true}};
		// case FETCH_POST_SUCCESS:
		// 	return { ...state, activePost: {post: action.payload, error:null, loading: false}};
		// case FETCH_POST_FAILURE:
		// 	error = action.payload || {message: action.payload.message};//2nd one is network or server down errors
		// 	return { ...state, activePost: {post: null, error:error, loading:false}};
		// case RESET_ACTIVE_POST:
		// 	return { ...state, activePost: {post: null, error:null, loading: false}};
		//
		// case CREATE_POST:
		// 	return {...state, newPost: {...state.newPost, loading: true}}
		// case CREATE_POST_SUCCESS:
		// 	return {...state, newPost: {post:action.payload, error:null, loading: false}}
		// case CREATE_POST_FAILURE:
		// 	error = action.payload || {message: action.payload.message};//2nd one is network or server down errors
		// 	return {...state, newPost: {post:null, error:error, loading: false}}
		// case RESET_NEW_POST:
		// 	return {...state,  newPost:{post:null, error:null, loading: false}}
		//
		//
		// case DELETE_POST:
		// 	return {...state, deletedPost: {...state.deletedPost, loading: true}}
		// case DELETE_POST_SUCCESS:
		// 	return {...state, deletedPost: {post:action.payload, error:null, loading: false}}
		// case DELETE_POST_FAILURE:
		// 	error = action.payload || {message: action.payload.message};//2nd one is network or server down errors
		// 	return {...state, deletedPost: {post:null, error:error, loading: false}}
		// case RESET_DELETED_POST:
		// 	return {...state,  deletedPost:{post:null, error:null, loading: false}}
		//
		// case VALIDATE_POST_FIELDS:
		// 	return {...state, newPost:{...state.newPost, error: null, loading: true}}
		// case VALIDATE_POST_FIELDS_SUCCESS:
		// 	return {...state, newPost:{...state.newPost, error: null, loading: false}}
		// case VALIDATE_POST_FIELDS_FAILURE:
		// 	let result = action.payload;
		// 	if(!result) {
		// 		error = {message: action.payload.message};
		// 	} else {
		// 		error = {title: result.title, categories: result.categories, description: result.description};
		// 	}
		// 	return {...state, newPost:{...state.newPost, error: error, loading: false}}
		// case RESET_POST_FIELDS:
		// 	return {...state, newPost:{...state.newPost, error: null, loading: null}}
		// default:
		// 	return state;
	}
}
