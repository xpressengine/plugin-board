import React from 'react';
import ReactDOM from 'react-dom';
import { Router, Route, Link, hashHistory } from 'react-router';

import { createStore } from 'redux';
import { Provider } from 'react-redux';
import counterApp from './reducers';

//pages
import Container from './components/common/Container';
import ListPage from './components/list/ListPage';
import CreatePage from './components/write/CreatePage';
import DetailPage from './components/detail/DetailPage';

const store = createStore(counterApp);

import "../css/board.css";

ReactDOM.render(
	<Provider store={store}>
		<Router history={hashHistory} component={Container}>
			<Route path='/' component={ListPage} />
			<Route path='/list' component={ListPage} />
			<Route path='/create' component={CreatePage} />
			<Route path='/deatil' component={DetailPage} />
		</Router>
	</Provider>
	, document.getElementById('boardContainer'));
