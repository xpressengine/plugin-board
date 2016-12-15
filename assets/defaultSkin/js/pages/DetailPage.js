import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import DetailView from './../components/detail/DetailView';
import BoardListContainer from './../containers/BoardListContainer';

import BoardIndex from './BoardIndex';

export default class DetailPage extends Component {
	render() {
		return (
			<div>
				<DetailView />
				<BoardIndex />
			</div>
		);
	}
}
