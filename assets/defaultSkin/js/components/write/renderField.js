import React, { Component, PropTypes } from 'react';

const renderField = ({ input, label, type, meta: { touched, error, warning } }) => {

	console.log('input', input);

	return (
		<div>
			<div>
				<input {...input} className="xe-form-control" placeholder={label} type={type}/>
				{touched && ((error && <span>{error}</span>) || (warning && <span>{warning}</span>))}
			</div>
		</div>
	)
}

export default renderField;