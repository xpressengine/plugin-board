import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import DetailContainer from './../containers/DetailContainer';

import BoardIndex from './BoardIndex';

export default class DetailPage extends Component {
	render() {
		return (
			<div>
				<DetailContainer />
				<BoardIndex />
			</div>
		);
	}
}
