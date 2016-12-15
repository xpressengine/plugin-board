import moment from 'moment';

export const timeAgo = (date) => {
	var isTimestamp = (parseInt(date) == date);

	if (isTimestamp) {
		date = moment.unix(date);
	} else {
		date = moment(date);
	}

	return date.fromNow();
};