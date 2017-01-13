import { combineReducers } from 'redux';
import { routerReducer } from 'react-router-redux'
import BoardListReducer from './BoardListReducer';
import BoardViewReducer from './BoardViewReducer';
import BoardEditReducer from './BoardEditReducer';
import BoardWriteReducer from './BoardWriteReducer';
import { reducer as formReducer } from 'redux-form';

const rootReducer = combineReducers({
	list: BoardListReducer,
	view: BoardViewReducer,
	edit: BoardEditReducer,
	write: BoardWriteReducer,
	routing: routerReducer,
	form: formReducer
});

export default rootReducer;