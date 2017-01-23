import React, { Component, PropTypes } from 'react';
import { reduxForm, Field } from 'redux-form';
import { updateBoard } from './../../actions/boardEditAction';
import Spinner from './../Spinner';
import renderField from './renderField';
import renderTextArea from './renderTextArea';
import _ from 'lodash';

import Dropdown from './../Dropdown';

class EditForm extends Component {

	static contextTypes = {
		router: PropTypes.object
	}

	static propTypes = {
		fields: PropTypes.array.isRequired,
		handleSubmit: PropTypes.func.isRequired,
		submitting: PropTypes.bool.isRequired
	}

	constructor() {
		super();

		this.handleSelect = ::this.handleSelect
	}

	componentWillMount() {
		const id = this.context.router.params.id;
		
		this.props.fetchView(id);
	}

	componentWillReceiveProps(nextProps) {
		console.log('componentWillReceiveProps', this.props, nextProps);

		// if (nextProps.item && !nextProps.error) {
		// 	this.context.router.push('/');
		// }
	}

	changeField(field, e) {

		console.log(field, e.target.value);

		this.props.changeFormField({field, value: e.target.value});
	}

	handleSelect(categoryItemId) {
		this.props.changeFormField({field: 'categoryItemId', value: categoryItemId});

	}

	validateAndUpdateBoard(values, dispatch, id) {
		values.slug = 'testSlug';
		values.id = id;
		console.log('values', values, id);

		return dispatch(updateBoard(id, values));
	}

	render() {
		const { handleSubmit, submitting, initialValues } = this.props;
		// const { fields: { title, content, slug, categoryItemId }, handleSubmit, load, submitting } = this.props;
		const id = this.context.router.params.id;

		console.log('this.props', this.props);
		// console.log('fields', fields);
		// console.log('id', id);

		console.log('initialValues', initialValues);

		if(this.props.err) {
			XE.toast('', this.props.err.message);
		}

		if(this.props.loading) {
			return (
				<Spinner />
			)
		}


		// this.props.initialize("editForm", { content: initialValues.content, title: initialValues.title}, ['title', 'content', 'slug', 'categoryItemId']);

		return (
			<div className="board_write">
				<form onSubmit={handleSubmit((values, dispatch) => { this.validateAndUpdateBoard(values, dispatch, id) })}>
					<div className="write_header">
						{
							(() => {
								let categories = this.props.categories;

								if(categories.length > 0) {
									if(!_.find(categories, {value: ''})) {
										categories.unshift({text: '전체보기', value: ''});
									}

									return (
										<div className="write_category">
											<Dropdown optionList={categories} handleSelect={this.handleSelect} selected={parseInt(this.props.item.category.itemId, 10)} />
										</div>
									)
								}
							})()
						}
						<div className="write_title">

							{
								//TODO
								(() => {
									if(1 !== 1) {
										return (
											<div className="temp_save">
												<a href="#" className="temp_save_num"><strong>3</strong>개의 임시 저장 글</a>
											</div>
										)
									}
								})()
							}

							<Field
								name="title"
								component={ renderField }

								type="text"
								label="제목을 입력하세요"
							/>

						</div>
					</div>
					<div className="write_body">
						<div className="write_form_editor">

							<Field
								name="content"
								component={ renderTextArea }

								label="내용을 입력하세요"
							/>

						</div>
					</div>
					<div className="write_footer">

						{
							(() => {
								if(1 !== 1) {
									return (
										<div className="write_form_input">
											<div className="xe-form-inline">
												<input type="text" className="xe-form-control" placeholder="이름" title="이름" />
												<input type="text" className="xe-form-control" placeholder="비밀번호" title="비밀번호" />
												<input type="text" className="xe-form-control" placeholder="이메일 주소" title="이메일 주소" />
											</div>
										</div>
									) ;
								}
							})()
						}

						<div className="write_form_option">
							<div className="xe-form-inline">
								<label className="xe-label">
									<input type="checkbox" />
									<span className="xe-input-helper"></span>
									<span className="xe-label-text">댓글허용</span>
								</label>
								<label className="xe-label">
									<input type="checkbox" />
									<span className="xe-input-helper"></span>
									<span className="xe-label-text">비밀글</span>
								</label>
							</div>
						</div>
						<div className="write_form_btn nologin">
							<a href="#" className="bd_btn btn_preview">미리보기</a>
							<button type="submit" className="bd_btn btn_submit">등록</button>
						</div>
					</div>
				</form>
			</div>
		);
	}
}

export default EditForm;