import React from 'react';
import { Path } from 'react-hash-router';

import Container from './components/common/Container';
import ListPage from './components/list/ListPage';
import CreatePage from './components/write/CreatePage';

export default new Path('*', Container, [
	new Path('/list', ListPage),
	new Path('/create', CreatePage),
]);