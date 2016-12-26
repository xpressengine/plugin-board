import React from 'react';
import { Route, IndexRoute } from 'react-router';

import Container from './pages/Container';
import BoardIndex from './pages/BoardIndex';
import WritePage from './pages/WritePage';
import EditPage from './pages/EditPage';
import DetailPage from './pages/DetailPage';
import NotFoundPage from './pages/NotFoundPage';

export default (
	<Route path='/' component={Container}>
		<IndexRoute component={BoardIndex} />
		<Route path='list' component={BoardIndex} />
		<Route path='write' component={WritePage} />
		<Route path='edit/:id' component={EditPage} />
		<Route path='detail/:id' component={DetailPage} />
		<Route path='*' component={NotFoundPage} />
	</Route>
)