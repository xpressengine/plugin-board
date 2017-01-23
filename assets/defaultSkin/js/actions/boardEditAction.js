import { Observable } from 'rxjs';
import { ajax } from 'rxjs/observable/dom/ajax';
import 'rxjs/operator/map';
import 'rxjs/operator/catch';

export const UPDATE_BOARD = "UPDATE_BOARD";
export const UPDATE_BOARD_SUCCESS = "UPDATE_BOARD_SUCCESS";
export const UPDATE_BOARD_FAILURE = "UPDATE_BOARD_FAILURE";

export const updateBoardEpic = action$ =>
	action$.ofType(UPDATE_BOARD)
		.mergeMap(action =>
			Observable::ajax({
				url: Common.get('apis').update.replace('[id]', action.id),
				method: 'PUT',
				body: action.payload,
				headers: Common.get('ajaxHeaders')
			})
				.map(data => updateBoardSuccess(data))
				.catch(error => Observable.of(updateBoardFailure(error)))
		);

export const updateBoard = (id, data) => ({
	type: UPDATE_BOARD,
	payload: data,
	id
})

const updateBoardSuccess = (data) => ({
	type: UPDATE_BOARD_SUCCESS,
	payload: data.response
})

export const updateBoardFailure = (error) => ({
	type: UPDATE_BOARD_FAILURE,
	payload: error.xhr.response
})