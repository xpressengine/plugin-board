import React, { Component, PropTypes } from 'react';
import { reduxForm, Field } from 'redux-form';
import Spinner from './../Spinner';
import renderField from './renderField';
import renderTextArea from './renderTextArea';
import _ from 'lodash';

import Dropdown from './../Dropdown';

class EditForm extends Component {

	static contextTypes = {
		router: PropTypes.object
	}

	constructor() {
		super();
	}

	componentWillMount() {
		const id = this.context.router.params.id;
		
		this.props.fetchView(id);
	}

	render() {
		if(this.props.loading) {
			return (
				<Spinner />
			)
		}

		console.log(this.props);

		const initialValues = {
			title: 'test',
			content: 'content',
			slug: 'testSlug',
			category: 'testCategory'
		}

		return (
			<div className="board_write">
				<form initialValues={initialValues}>
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
											<Dropdown optionList={categories} handleSelect={this.props.handleSelect} selected={parseInt(this.props.item.category.itemId, 10)} />
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
								value="test"
								type="text"
								component={ renderField }
								label="제목을 입력하세요"
							/>

						</div>
					</div>
					<div className="write_body">
						<div className="write_form_editor">

							<Field
								name="content"
								component={ renderTextArea }
								value={ this.props.item.content }
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
							<a href="#" className="bd_btn btn_submit">등록</a>
						</div>
					</div>
				</form>
			</div>
		);
	}
}

export default EditForm;