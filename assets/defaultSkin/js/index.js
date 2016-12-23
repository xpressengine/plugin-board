import React from 'react';
import ReactDOM from 'react-dom';
import { Router, hashHistory } from 'react-router';
import { createStore } from 'redux';
import { Provider } from 'react-redux';
import { syncHistoryWithStore } from 'react-router-redux';

import routes from './routes';
import rootReducer from './reducers';

import moment from 'moment';

import "../css/board.css";

moment.locale(XE.getLocale());

const store = createStore(rootReducer);
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
