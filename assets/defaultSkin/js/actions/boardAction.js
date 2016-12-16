//Board list
// export const FETCH_BOARD_LIST = 'FETCH_BOARD_LIST';
export const FETCH_BOARD_INDEX_SUCCESS = 'FETCH_BOARD_INDEX_SUCCESS';
export const FETCH_BOARD_INDEX_FAILURE = 'FETCH_BOARD_INDEX_FAILURE';
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

export const fetchBoardIndexSuccess = (response) => {
	console.log('response :: ', response);

	return {
		type: FETCH_BOARD_INDEX_SUCCESS,
		payload: response
	};
}

export const fetchBoardIndexFailure = (err) => {
	return {
		type: FETCH_BOARD_INDEX_FAILURE,
		payload: error
	};
}

export const fetchDetailSuccess = () => {

}