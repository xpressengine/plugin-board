import React from 'react';
import ReactDOM from 'react-dom';

import Router from 'react-hash-router';
import Routes from './routes';

import '../css/board.css';

Router.run(Routes, function(RootComponent, props) {
	ReactDOM.render(<RootComponent {...props}/>, document.getElementById('boardContainer'));
});
