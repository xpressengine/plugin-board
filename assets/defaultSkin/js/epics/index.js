import { combineEpics } from 'redux-observable';
import { fetchBoardIndexEpic } from '../actions/boardListAction';
import { fetchViewEpic } from '../actions/boardViewAction';

const rootEpics = combineEpics(fetchBoardIndexEpic, fetchViewEpic);

export default rootEpics;