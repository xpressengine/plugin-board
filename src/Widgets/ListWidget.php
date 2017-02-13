<?php
namespace Xpressengine\Plugins\Board\Widgets;

use Carbon\Carbon;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Widget\AbstractWidget;
use View;
use Xpressengine\Menu\Models\MenuItem;

class ListWidget extends AbstractWidget
{
    protected static $viewAlias = 'board::views.widgets.list';

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        $widgetConfig = $this->setting();

        $boardHandler = app('xe.board.handler');
        $configHandler = app('xe.board.config');
        $urlHandler = app('xe.board.url');

        $menuItem = MenuItem::find($widgetConfig['board_id']);

        $boardConfig = $configHandler->get($menuItem->id);
        $boardId = $boardConfig->get('boardId');
        $take = $widgetConfig['take'];
        $recent_date = (int)$widgetConfig['recent_date'];
        $orderType = $widgetConfig['order_type'];

        /**
         * config 할수 있는것
         * 몇개, 게시판 아이디, 최근 몇일, 정렬 방법
         */

        /** @var \Xpressengine\Database\DynamicQuery $query */
        $query = $boardHandler->getModel($boardConfig);
        $query = $query->where('instanceId', $boardId);
        $query = $query->leftJoin(
            'board_gallery_thumbs',
            sprintf('%s.%s', $query->getQuery()->from, 'id'),
            '=',
            sprintf('%s.%s', 'board_gallery_thumbs', 'targetId')
        );
        $query = $query->visible();

        //$recent_date
        if ($recent_date !== 0) {
            $current = Carbon::now();
            $query = $query->where('createdAt', '>=', $current->addDay(-1 * $recent_date)->toDateString() . ' 00:00:00')
                ->where('createdAt', '<=', $current->addDay($recent_date)->toDateString() . ' 23:59:59');
        }

        //$orderType
        if ($orderType == '') {
            $query = $query->orderBy('head', 'desc');
        } elseif ($orderType == 'assentCount') {
            $query = $query->orderBy('assentCount', 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recentlyCreated') {
            $query = $query->orderBy(Board::CREATED_AT, 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recentlyUpdated') {
            $query = $query->orderBy(Board::UPDATED_AT, 'desc')->orderBy('head', 'desc');
        }

        $query = $query->take($take);

        $list = $query->get();

        $urlHandler = new UrlHandler($boardConfig);

        return $view = View::make(sprintf('%s.widget', static::$viewAlias), [
            'list' => $list,
            'boardConfig' => $boardConfig,
            'menuItem' => $menuItem,
            'widgetConfig' => $widgetConfig,
            'urlHandler' => $urlHandler,
        ]);
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
        return $view = View::make(sprintf('%s.%s', static::$viewAlias, 'setting'), [
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
            if ($boardName === null || $boardName === '') {
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
