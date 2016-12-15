import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import WriteForm from './../components/write/WriteForm';
import BoardListContainer from './../containers/BoardListContainer';

export default class WritePage extends Component {
	render() {
		return (
			<div>
				<WriteForm />
			</div>
		);
	}
}