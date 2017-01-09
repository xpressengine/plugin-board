import { combineReducers } from 'redux';
import { routerReducer } from 'react-router-redux'
import BoardListReducer from './BoardListReducer';
import BoardViewReducer from './BoardViewReducer';
import BoardEditReducer from './BoardEditReducer';
import { reducer as formReducer } from 'redux-form';

const rootReducer = combineReducers({
	list: BoardListReducer,
	view: BoardViewReducer,
	edit: BoardEditReducer,
	routing: routerReducer,
	form: formReducer
});

export default rootReducer;