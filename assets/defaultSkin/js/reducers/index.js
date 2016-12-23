import { combineReducers } from 'redux';
import { routerReducer } from 'react-router-redux'
import BoardListReducer from './BoardListReducer';
import BoardViewReducer from './BoardViewReducer';

const rootReducer = combineReducers({
	list: BoardListReducer,
	view: BoardViewReducer,
	routing: routerReducer
});

export default rootReducer;