import React, { Component } from 'react';

class Dropdown extends Component {

	static propTypes = {
		optionList: React.PropTypes.array.isRequired,
		handleClick: React.PropTypes.func
	};

	constructor(props) {
		super(props);
	}

	render() {
		return(
			<div className="xe-dropdown">
				<button className="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">전체보기</button>
				<ul className="xe-dropdown-menu">
					<li className="on"><a href="#" onClick={ this.handleClick }>전체보기</a></li>
					{
						this.props.optionList.map((obj, i) => {
								return (
									<li><a href="#" onClick={this.props.handleClick.bind(this, obj.value)}>{ obj.text }</a></li>
								)
						})
					}
				</ul>
			</div>
		)
	}
}

export default Dropdown;