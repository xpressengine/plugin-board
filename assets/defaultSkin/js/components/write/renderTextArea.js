import React, { Component, PropTypes } from 'react';

const renderTextArea = ({ input, label, meta: { touched, error, invalid, warning } }) => (
	<div className={`form-group ${touched && invalid ? 'has-error' : ''}`}>
		<div>
			<textarea {...input} className="xe-form-control"  placeholder={label} />
			<div className="help-block">
				{touched && ((error && <span>{error}</span>) || (warning && <span>{warning}</span>))}
			</div>
		</div>
	</div>
)

export default renderTextArea;