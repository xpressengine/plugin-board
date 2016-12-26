import React, { Component } from 'react';

class Dropdown extends Component {

	static propTypes = {
		optionList: React.PropTypes.array.isRequired,
		handleSelect: React.PropTypes.func,
	};

	constructor(props) {
		super(props);

		this.state = {
			selectedText: '전체보기',
			selectedValue: ''
		};
	}

	handleSelect(obj, e) {
		e.preventDefault();

		this.setState((s, p) => {
			s.selectedValue = obj.value;
			s.selectedText = obj.text;
		});

		this.props.handleSelect(obj.value);
	}

	render() {
		return(
			<div className="xe-dropdown">
				<button className="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">{this.state.selectedText}</button>
				<ul className="xe-dropdown-menu">
					{
						this.props.optionList.map((obj, i) => {

							var on = (i === 0 && !this.state.selectedValue || this.state.selected === obj.value)? "on" : '';
							
							return (
								<li className={on}><a href="#" onClick={this.handleSelect.bind(this, obj)}>{ obj.text }</a></li>
							)
						})
					}
				</ul>
			</div>
		)
	}
}

export default Dropdown;