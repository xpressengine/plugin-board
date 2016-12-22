import {
	FETCH_BOARD_INDEX_SUCCESS, FETCH_BOARD_INDEX_FAILURE,
	CHECK_ALL, UNCHECK_ALL, CHECK_ROW, UNCHECK_ROW,
	SHOW_MANAGEMENT, HIDE_MANAGEMENT,
	FETCH_BOARD_SUCCESS, FETCH_BOARD_FAILURE
} from '../actions/boardAction';

const INITIAL_STATE = {
	index: {
		paginate: {
			currentPage: 0,
			from: 0,
			lastPage: 0,
			nextPageUrl: '',
			perPage: 0,
			to: 0,
			total: 0,
			perPageBlockCount: 0
		},
		boardList: [],
		categories: [],
		error: null,
		loading: false,
	},
	managementStatus: 'none',
	checkedMap: {},
	detail: {
		board: null, error: null, loading: false
	},
};

export default function(state = INITIAL_STATE, action) {
	let error;

	switch(action.type) {
		case FETCH_BOARD_INDEX_SUCCESS:// return list of posts and make loading = false

			var checkedMap = {};
			var boardList = action.payload.paginate.data;
			var resPaginate = action.payload.paginate;
			var paginate = {
				currentPage: resPaginate.current_page,
				from: resPaginate.from,
				lastPage: resPaginate.last_page,
				nextPageUrl: resPaginate.next_page_url,
				perPage: resPaginate.per_page,
				to: resPaginate.to,
				total: resPaginate.total,
				perPageBlockCount: 10
			};

			boardList.map((obj) => {
				checkedMap[obj.id] = false;
			});

			return { ...state, index: {boardList: boardList, paginate: paginate, categories: action.payload.categories, error:null, loading: false, }, checkedMap: checkedMap };

		case CHECK_ALL:
			var checkedMap = {};
			var boardList = state.index.boardList;

			boardList.map((obj, i) => {
				checkedMap[obj.id] = true;
			});

			return { ...state, checkedMap: checkedMap};

		case UNCHECK_ALL:
			var checkedMap = {};
			var boardList = state.index.boardList;

			boardList.map((obj, i) => {
				checkedMap[obj.id] = false;
			});

			return { ...state, checkedMap: checkedMap};

		case CHECK_ROW:
			var checkedMap = {};
			var stateCheckedMap = state.checkedMap;

			stateCheckedMap[action.id] = true;

			checkedMap = Object.assign({}, checkedMap, stateCheckedMap);

			return { ...state, checkedMap: checkedMap};

		case UNCHECK_ROW:
			var checkedMap = {};
			var stateCheckedMap = state.checkedMap;

			stateCheckedMap[action.id] = false;

			checkedMap = Object.assign({}, checkedMap, stateCheckedMap);

			return { ...state, checkedMap: checkedMap};

		case SHOW_MANAGEMENT:
			return { ...state, managementStatus: action.display }

		case HIDE_MANAGEMENT:
			return { ...state, managementStatus: action.display }

		default:
			return state;
	}
}
