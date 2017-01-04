import { Observable } from 'rxjs';
import { ajax } from 'rxjs/observable/dom/ajax';

//Fetch board
export const FETCH_VIEW = 'FETCH_VIEW';
export const FETCH_VIEW_SUCCESS = 'FETCH_VIEW_SUCCESS';
export const FETCH_VIEW_FAILURE = 'FETCH_VIEW_FAILURE';

export const fetchViewEpic = (action$) =>
	action$.ofType(FETCH_VIEW)
		.mergeMap(action =>
			ajax({ url: Common.get('apis').view.replace('[id]', action.id), method: 'GET'})
				.map(e => fetchViewSuccess(e))
				.catch(err => Observable.of(fetchViewFailure(err)))
		);

export const fetchView = (id) => ({
	type: FETCH_VIEW,
	id
});

export const fetchViewSuccess = (e) => ({
	type: FETCH_VIEW_SUCCESS,
	payload: e.response
})

export const fetchViewFailure = (error) => ({
	type: FETCH_VIEW_FAILURE,
	payload: error.xhr.response
})