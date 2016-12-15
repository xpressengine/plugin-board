//Board list
export const FETCH_BOARD_LIST = 'FETCH_BOARD_LIST';
export const FETCH_BOARD_LIST_SUCCESS = 'FETCH_BOARD_LIST_SUCCESS';
export const FETCH_BOARD_LIST_FAILURE = 'FETCH_BOARD_LIST_FAILURE';
export const RESET_BOARD_LIST = 'RESET_BOARD_LIST';

//Create new board
export const CREATE_BOARD = 'CREATE_BOARD';
export const CREATE_BOARD_SUCCESS = 'CREATE_BOARD_SUCCESS';
export const CREATE_BOARD_FAILURE = 'CREATE_BOARD_FAILURE';
export const RESET_NEW_BOARD = 'RESET_NEW_BOARD';

//Validate board fields like Title, Categries on the server
export const VALIDATE_BOARD_FIELDS = 'VALIDATE_BOARD_FIELDS';
export const VALIDATE_BOARD_FIELDS_SUCCESS = 'VALIDATE_BOARD_FIELDS_SUCCESS';
export const VALIDATE_BOARD_FIELDS_FAILURE = 'VALIDATE_BOARD_FIELDS_FAILURE';
export const RESET_BOARD_FIELDS = 'RESET_BOARD_FIELDS';

//Fetch board
export const FETCH_BOARD = 'FETCH_BOARD';
export const FETCH_BOARD_SUCCESS = 'FETCH_BOARD_SUCCESS';
export const FETCH_BOARD_FAILURE = 'FETCH_BOARD_FAILURE';
export const RESET_ACTIVE_BOARD = 'RESET_ACTIVE_BOARD';

//Delete board
export const DELETE_BOARD = 'DELETE_BOARD';
export const DELETE_BOARD_SUCCESS = 'DELETE_BOARD_SUCCESS';
export const DELETE_BOARD_FAILURE = 'DELETE_BOARD_FAILURE';
export const RESET_DELETED_BOARD = 'RESET_DELETED_BOARD';

export const fetchBoardList = (options) => {
	return {
		type: FETCH_BOARD_LIST,
		options: {
			url: Common.get('apis').list,
			dataType: 'json',
			data: {}
		}
	};
}

export const fetchBoardListSuccess = (response) => {
	console.log('response : ', response);

	return {
		type: FETCH_BOARD_LIST_SUCCESS,
		payload: response.paginate.data
	};
}

export const fetchBoardListFailure = (err) => {
	return {
		type: FETCH_POSTS_FAILURE,
		payload: error
	};
}