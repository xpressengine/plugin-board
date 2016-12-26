import React from 'react';
import ReactDOM from 'react-dom';

class EditForm extends React.Component {

	render() {
		return (
			<div className="board_write">
				<div className="write_header">
					<div className="write_category">
						<div className="xe-dropdown">
							<button className="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">카테고리</button>
							<ul className="xe-dropdown-menu">
								<li className="on"><a href="#">카테고리</a></li>
								<li><a href="#">카테고리</a></li>
							</ul>
						</div>
					</div>
					<div className="write_title">
						<div className="temp_save">
							<a href="#" className="temp_save_num"><strong>3</strong>개의 임시 저장 글</a>
						</div>
						<input type="text" className="xe-form-control" placeholder="제목을 입력하세요" title="제목" />
					</div>
				</div>
				<div className="write_body">
					<div className="write_form_editor">
						<div className="cke">임시 에디터</div>
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
						<a href="#" className="bd_btn btn_submit">등록</a>
					</div>
				</div>
			</div>
		);
	}
}

export default EditForm;