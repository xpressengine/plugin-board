import { Observable } from 'rxjs';
import { ajax } from 'rxjs/observable/dom/ajax';

export const EDIT_BOARD = "EDIT_BOARD";
export const EDIT_BOARD_SUCCESS = "EDIT_BOARD_SUCCESS";
export const EDIT_BOARD_FAILURE = "EDIT_BOARD_FAILURE";

export const editBoardEpic = action$ =>
	action$.ofType(EDIT_BOARD)
		.mergeMap(action =>
			ajax({ url: Common.get('apis').update, type: 'put', body: action.payload })
				.mergeMap(data => editBoardSuccess(data))
				.catch(error => Observable.of(editBoardFailure(error)))
		);

const editBoardSuccess = (data) => ({
	type: EDIT_BOARD_SUCCESS,
	payload: data.response
})

export const editBoardFailure = (error) => ({
	type: EDIT_BOARD_FAILURE,
	payload: error.xhr.response
})