import React from 'react';
import ReactDOM from 'react-dom';
import { RouteHandler } from 'react-hash-router';

class Container extends React.Component {
	render() {
		return (
			<div>
				<RouteHandler />
			</div>
		);
	}
}

export default Container;