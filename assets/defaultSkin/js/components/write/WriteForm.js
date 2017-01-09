import React from 'react';
import { reduxForm, Field } from 'redux-form';
import renderField from './renderField';
import renderTextArea from './renderTextArea';
import { createBoardContents } from './../../actions/boardWriteAction';
import _ from 'lodash';

import Dropdown from './../Dropdown';

const { DOM: { input } } = React;

function validate(values) {
	console.log('v', JSON.stringify(values));
}


const validateAndCreateBoard = (values, dispatch, selectedCategory) => {
	return dispatch(createBoardContents(values));
}


class WriteForm extends React.Component {

	constructor() {
		super();
	}

	componentWillMount() {
		//카테고리 정보
		this.props.fetchBoardIndex();
	}

	handleSelect(value) {

	}

	render() {
		const { handleSubmit, submitting } = this.props;

		return (
			<div className="board_write">
				<form onSubmit={handleSubmit(validateAndCreateBoard)}>
					<div className="write_header">
					{
						(() => {
							let categories = this.props.categories;

							if(categories.length > 0) {
								if(!_.find(categories, {value: ''})) {
									categories.unshift({text: '전체보기', value: ''});
								}

								return (
									<Field name="categoryItemId">
										<div className="write_category">
											<Dropdown optionList={categories} handleSelect={this.handleSelect.bind(this)} />
										</div>
									</Field>
								)
							}
						})()
					}
					<div className="write_title">
						<div className="temp_save">
							<a href="#" className="temp_save_num"><strong>3</strong>개의 임시 저장 글</a>
						</div>

						<Field
							name="title"
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
								label="내용을 입력하세요"
							/>
						</div>
					</div>
					<div className="write_footer">
						<div className="write_form_input">
							<div className="xe-form-inline">
								<input type="text" className="xe-form-control" placeholder="이름" title="이름" />
								<input type="text" className="xe-form-control" placeholder="비밀번호" title="비밀번호" />
								<input type="text" className="xe-form-control" placeholder="이메일 주소" title="이메일 주소" />
							</div>
						</div>
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
							<button
								type="submit"
								className="bd_btn btn_submit"
								disabled={submitting}>등록</button>
						</div>
					</div>
				</form>
			</div>
		);
	}
}

export default reduxForm({
	form: 'WriteForm',  // a unique identifier for this form
	validate,                // <--- validation function given to redux-form
	// warn                     // <--- warning function given to redux-form
})(WriteForm);