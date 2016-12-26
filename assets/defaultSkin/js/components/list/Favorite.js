import React, { Component } from 'react';
import ReactDOM from 'react-dom';

export default class Favorite extends Component {

	static propTypes = {
		id: React.PropTypes.string.isRequired,
		favorite: React.PropTypes.string
	};

	constructor() {
		super();

		this.handleFavorite = ::this.handleFavorite;
	}

	handleFavorite(e) {
		e.preventDefault();

		let target = e.target;

		this.props.setFavorite({
			id: this.props.id,
			isFavorite: !$(target).hasClass('on')
		});
	}

	render () {
		let on = (this.props.favorite === null || this.props.favorite === '')? '' : 'on';

		return (
			<td className="favorite xe-hidden-xs"><a href="#" title="즐겨찾기 체크" onClick={this.handleFavorite}><i className={`xi-star-o ${on}`}></i><span className="xe-sr-only">즐겨찾기 체크</span></a></td>
		);
	}
}