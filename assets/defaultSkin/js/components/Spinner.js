import React from 'react';
import style from './../../css/spinner.css';

export default class Spinner extends React.Component {
	render() {

		/**
		 <div className={style.spinner}>
		 <div className={style.rect1}></div>
		 <div className={style.rect2}></div>
		 <div className={style.rect3}></div>
		 <div className={style.rect4}></div>
		 <div className={style.rect5}></div>
		 </div>
		 * */

		return (
			<div>
				<div class="xe-loading xe-fixed">
					<div class="xe-loading-inner">
						<span class="xe-sr-only">Loading...</span>
					</div>
				</div>
				<div class="dim" style="display:block"></div>
			</div>
		);
	}
}
