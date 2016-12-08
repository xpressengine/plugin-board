import React from 'react';
import ReactDOM from 'react-dom';
import Part from './Part';

import Header from'./Header';
import Footer from'./Footer';

export default class BoardList extends React.Component {

	constructor(props) {
		super(props);
	}

	render() {
		return (
			<div>
				<Header />

				<ul>
					<Part />
				</ul>

				<Footer />
			</div>
		);
	}
};
