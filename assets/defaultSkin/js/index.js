import React from 'react';
import ReactDOM from 'react-dom';
import { Router, hashHistory } from 'react-router';
import { createStore, applyMiddleware } from 'redux';
import { Provider } from 'react-redux';
import { syncHistoryWithStore } from 'react-router-redux';
import { createEpicMiddleware } from 'redux-observable';

import routes from './routes';
import rootReducer from './reducers';
import rootEpic from './epics';

import moment from 'moment';

import "../css/board.css";

moment.locale(XE.getLocale());

let store;
const epicMiddleware = createEpicMiddleware(rootEpic);

if(location && location.hostname === 'localhost') {
	store = createStore(rootReducer, applyMiddleware(epicMiddleware), window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__());

} else {
	store = createStore(rootReducer, applyMiddleware(epicMiddleware));

}

const history = syncHistoryWithStore(hashHistory, store, {
	selectLocationState: (state) => {
		return state.routing;
	}
});

ReactDOM.render(
	<Provider store={store}>
		<Router history={history} routes={routes} />
	</Provider>
	, document.getElementById('boardContainer'));

