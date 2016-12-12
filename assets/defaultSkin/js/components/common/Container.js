import React from 'react';
import ReactDOM from 'react-dom';

class Container extends React.Component {
	render() {
		return (
			<div>
				{this.props.children}
			</div>
		);
	}
}

export default Container;