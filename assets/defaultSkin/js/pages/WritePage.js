import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import WriteForm from './../components/write/WriteForm';
import WriteContainer from './../containers/WriteContainer';

export default class WritePage extends Component {
	render() {
		return (
			<div>
				<WriteContainer />
			</div>
		);
	}
}