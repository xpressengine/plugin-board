import React from 'react';
import { Router, Route, Link, hashHistory, IndexRoute } from 'react-router';

import Container from './pages/Container';
import BoardIndex from './pages/BoardIndex';

/*
 import WritePage from './pages/WritePage';
 import DetailPage from './pages/DetailPage';

 <Route path='write' component={WritePage} />
 <Route path='detail/:id' component={DetailPage} />
* */


export default (
	<Route path='/' component={Container}>
		<IndexRoute component={BoardIndex} />
		<Route path='list' component={BoardIndex} />

	</Route>
)