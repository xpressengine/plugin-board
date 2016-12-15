import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import BoardListContainer from './../containers/BoardListContainer';

export default class BoardIndex extends Component {
	render() {
		return (
			<div>
				<BoardListContainer />
			</div>
		);
	}
}