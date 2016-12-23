//Fetch board
export const FETCH_BOARD = 'FETCH_BOARD';
export const FETCH_BOARD_SUCCESS = 'FETCH_BOARD_SUCCESS';
export const FETCH_BOARD_FAILURE = 'FETCH_BOARD_FAILURE';
export const RESET_ACTIVE_BOARD = 'RESET_ACTIVE_BOARD';


export const fetchViewSuccess = (response) => {
	console.log('view response', response);

	return {
		type: FETCH_BOARD_SUCCESS,
		payload: response
	}
}

export const fetchDetailFailure = (err) => {
	return {
		type: FETCH_BOARD_FAILURE,
		payload: err
	}
}