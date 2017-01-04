import { Observable } from 'rxjs';
import { ajax } from 'rxjs/observable/dom/ajax';
import { objectToQuerystring } from './../utils';

export const FETCH_BOARD_INDEX = 'FETCH_BOARD_INDEX';
export const FETCH_BOARD_INDEX_SUCCESS = 'FETCH_BOARD_INDEX_SUCCESS';
export const FETCH_BOARD_INDEX_FAILURE = 'FETCH_BOARD_INDEX_FAILURE';

export const CHECK_ALL = 'CHECK_ALL';
export const UNCHECK_ALL = 'UNCHECK_ALL';
export const CHECK_ROW = 'CHECK_ROW';
export const UNCHECK_ROW = 'UNCHECK_ROW';

export const SHOW_MANAGEMENT = 'SHOW_MANAGEMENT';
export const HIDE_MANAGEMENT = 'HIDE_MANAGEMENT';

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

//Delete board
export const DELETE_BOARD = 'DELETE_BOARD';
export const DELETE_BOARD_SUCCESS = 'DELETE_BOARD_SUCCESS';
export const DELETE_BOARD_FAILURE = 'DELETE_BOARD_FAILURE';
export const RESET_DELETED_BOARD = 'RESET_DELETED_BOARD';

export const fetchBoardIndexEpic = action$ => {
	return action$.ofType(FETCH_BOARD_INDEX)
		.mergeMap(action => {
			console.log('action', action);

			return ajax({ url: Common.get('apis').index + action.query, method: 'GET',})
				.map(data => fetchBoardIndexSuccess(data))
				.catch(err => Observable.of(fetchBoardIndexFailure(err)))
			}
		);
}


export const fetchBoardIndex = (queryJSON) => ({
	type: FETCH_BOARD_INDEX,
	query: queryJSON? objectToQuerystring(queryJSON) : ''
});

export const fetchBoardIndexSuccess = (data) => ({
	type: FETCH_BOARD_INDEX_SUCCESS,
	payload: data.response
});

export const fetchBoardIndexFailure = (error) => ({
	type: FETCH_BOARD_INDEX_FAILURE,
	payload: error.xhr.response
});
