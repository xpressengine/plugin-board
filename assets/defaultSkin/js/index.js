import React from 'react';
import ReactDOM from 'react-dom';
import { Router, Route, Link, hashHistory, IndexRoute } from 'react-router';

import { createStore } from 'redux';
import { Provider } from 'react-redux';

import routes from './routes';
import rootReducer from './reducers';

import moment from 'moment';

import "../css/board.css";

moment.locale(XE.getLocale());

const store = createStore(rootReducer);

ReactDOM.render(
	<Provider store={store}>
		<Router history={hashHistory} routes={routes} />
	</Provider>
	, document.getElementById('boardContainer'));
