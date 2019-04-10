<?php
/**
 * ListWidget
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Components\Widgets\ArticleList;

use Carbon\Carbon;
use View;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Widget\AbstractWidget;

/**
 * ListWidget
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class ArticleListWidget extends AbstractWidget
{
    /**
     * @var string
     */
    protected static $path = 'board/components/Widgets/ArticleList';

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        $widgetConfig = $this->setting();

        /** @var \Xpressengine\Plugins\Board\Handler $boardHandler */
        $boardHandler = app('xe.board.handler');
        $configHandler = app('xe.board.config');
        $urlHandler = app('xe.board.url');
        if (!array_has($widgetConfig, 'board_id')) {
            $widgetConfig['board_id']['item'] = [];
        }

//        다중 선택으로 변환. 현재 셀렉트박스 muliple설정은 배열인경우 item값으로 넘어오므로 설정

        $boardIds = (is_array($widgetConfig['board_id'])) ?
            $widgetConfig['board_id']['item'] :
            (array)$widgetConfig['board_id'];

//        기존의 버젼과 대응해야하고 더보기 링크의 기본값을 위해서 대표 게시판 아이디를 선택
        $menuItem = MenuItem::find(($boardIds) ?
            array_first($boardIds) :
            Board::where('type', BoardModule::getId())->first()->instance_id);

//        현재 사용하지않지만 기존버젼 대응을위해 살림
        $boardConfig = $configHandler->get($menuItem->id);


//        아래 설정은 스킨에서 선택적으로 넣을 수 있음
        $take = $widgetConfig['take'] ?? null;
        $recent_date = (int)$widgetConfig['recent_date'] ?? 0;
        $orderType = $widgetConfig['order_type'] ?? '';

        $title = $widgetConfig['title'] !== '' ? $widgetConfig['title'] : '게시판';
        $more = $widgetConfig['more'] ?
            instance_route('index', [], $widgetConfig['more']) :
            instance_route('index', [], $menuItem->id);

        /**
         * config 할수 있는것
         * 몇개, 게시판 아이디, 최근 몇일, 정렬 방법
         *
         * 2019.04.10 다중 선택 변환으로 division이 아닌 해당 document 테이블만 조회하도록 변경
         */
        $model = new Board();
        /** @var \Xpressengine\Database\DynamicQuery $query */
        if (count($boardIds)) {
            $query = $model->whereIn('instance_id', $boardIds);
        } else {
            $query = $model->where('type', BoardModule::getId());
        }
        $query = $query->leftJoin(
            'board_gallery_thumbs',
            sprintf('%s.%s', $query->getQuery()->from, 'id'),
            '=',
            sprintf('%s.%s', 'board_gallery_thumbs', 'target_id')
        );
        $query = $query->visible();

        //$recent_date
        if ($recent_date !== 0) {
            $current = Carbon::now();
            $query = $query->where('created_at', '>=', $current->addDay(-1 * $recent_date)->toDateString() . ' 00:00:00')
                ->where('created_at', '<=', $current->addDay($recent_date)->toDateString() . ' 23:59:59');
        }

        //$orderType
        if ($orderType == '') {
            $query = $query->orderBy('head', 'desc');
        } elseif ($orderType == 'assent_count') {
            $query = $query->orderBy('assent_count', 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recentlyCreated') {
            $query = $query->orderBy(Board::CREATED_AT, 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recentlyUpdated') {
            $query = $query->orderBy(Board::UPDATED_AT, 'desc')->orderBy('head', 'desc');
        }

        if ($take) {
            $query = $query->take($take);
        }

        $list = $query->get();
        $list = $list->map(function ($item) use ($configHandler) {
            $item->boardConfig = $configHandler->get($item->instance_id);
            return $item;
        });

//        $urlHandler = new UrlHandler($boardConfig);

        return $this->renderSkin(
            [
                'list' => $list,
                'boardConfig' => $boardConfig,
                'menuItem' => $menuItem,
                'widgetConfig' => $widgetConfig,
                'urlHandler' => $urlHandler,
                'title' => $title,
                'more' => $more
            ]
        );
    }

    /**
     * 위젯 설정 페이지에 출력할 폼을 출력한다.
     *
     * @param array $args 설정값
     *
     * @return string
     */
    public function renderSetting(array $args = [])
    {
        return $view = View::make(sprintf('%s/views/setting', static::$path), [
            'args' => $args,
            'boardList' => $this->getBoardList(),
        ]);
    }

    /**
     * get board list
     *
     * @return array
     */
    protected function getBoardList()
    {
        $configHandler = app('xe.board.config');
        $boards = $configHandler->gets();
        $boardList = [];
        /** @var \Xpressengine\Config\ConfigEntity $config */
        foreach ($boards as $config) {
            $boardName = $config->get('boardName');
            if ($boardName === null || $boardName === '' || xe_trans($boardName) == null) {
                $menuItem = MenuItem::find($config->get('boardId'));
                $boardName = $menuItem->title;
            }

            $boardList[] = [
                'value' => $config->get('boardId'),
                'text' => $boardName,
            ];
        }
        return $boardList;
    }
}
