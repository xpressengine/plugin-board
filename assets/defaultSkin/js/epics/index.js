import { combineEpics } from 'redux-observable';
import { fetchBoardIndexEpic } from '../actions/boardListAction';
import { fetchViewEpic } from '../actions/boardViewAction';
import { createBoardContentsEpic } from '../actions/boardWriteAction';
import { editBoardEpic } from '../actions/boardEditAction';

const rootEpics = combineEpics(fetchBoardIndexEpic, fetchViewEpic, createBoardContentsEpic, editBoardEpic);

export default rootEpics;