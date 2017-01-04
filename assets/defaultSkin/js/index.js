import React from 'react';
import ReactDOM from 'react-dom';
import { Router, hashHistory } from 'react-router';
import { createStore, applyMiddleware } from 'redux';
import { Provider } from 'react-redux';
import { syncHistoryWithStore, routerMiddleware } from 'react-router-redux';
import { createEpicMiddleware } from 'redux-observable';

import routes from './routes';
import rootReducer from './reducers';
import rootEpics from './epics';

import moment from 'moment';

import "../css/board.css";

moment.locale(XE.getLocale());

let store;
const epicMiddleware = createEpicMiddleware(rootEpics);
const router = routerMiddleware(hashHistory);
const createStoreWithMiddleware = applyMiddleware(epicMiddleware, router)(createStore);

if(location && location.hostname === 'localhost') {
	store = createStoreWithMiddleware(rootReducer, window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__());
} else {
	store = createStoreWithMiddleware(rootReducer);
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

