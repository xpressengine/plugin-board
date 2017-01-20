import React, { Component, PropTypes } from 'react';
import _ from 'lodash';

class Dropdown extends Component {

	static propTypes = {
		optionList: PropTypes.array.isRequired,
		handleSelect: PropTypes.func,
		selected: PropTypes.number
	};

	constructor(props) {
		super(props);

		this.state = {
			selectedText: '전체보기',
			selectedValue: ''
		};
	}

	componentWillMount() {

		if(this.props.selected && this.props.optionList.length > 0) {
			const defaultSelected = _.find(this.props.optionList, {value: this.props.selected});

			this.handleSelect({
				text: defaultSelected.text,
				value: defaultSelected.value
			})
		}
	}

	handleSelect(obj, e) {

		if(e) {
			e.preventDefault();
		}

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
								<li key={i} className={on}><a href="#" onClick={this.handleSelect.bind(this, obj)}>{ obj.text }</a></li>
							)
						})
					}
				</ul>
			</div>
		)
	}
}

export default Dropdown;