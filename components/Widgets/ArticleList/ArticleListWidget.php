<?php
/**
 * ListWidget
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Components\Widgets\ArticleList;

use Carbon\Carbon;
use View;
use XEHub\XePlugin\XehubCustomDevelop\Models\Certificate;
use Xpressengine\Category\Models\Category;
use Xpressengine\Category\Models\CategoryItem;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Widget\AbstractWidget;

/**
 * ListWidget
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
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

        //다중 선택으로 변환. 현재 셀렉트박스 muliple설정은 배열인경우 item값으로 넘어오므로 설정
        $categorySelected = (is_array($widgetConfig['board_id'])) ?
            $widgetConfig['board_id']['item'] :
            (array)$widgetConfig['board_id'];

        //게시판 쿼리와 카테고리 쿼리를 각각 할 수 있도록 분리
        $boardIds = array_filter($categorySelected, function ($item) {
            return mb_substr($item, 0, 9) != 'category.';
        });

        $categoryIds = array_filter($categorySelected, function ($item) {
            return mb_substr($item, 0, 9) == 'category.';
        });

        //상위카테고리는 하위카테고리를 포함해야함
        $categoryIds = array_map(function ($item) {
            $item = CategoryItem::find(mb_substr($item, 9));
            if ($item === null) {
                return [];
            }

            return $item->getDescendantTree(true)->getNodes()->pluck('id');
        }, $categoryIds);

        $categoryIds = array_flatten($categoryIds);

        //기존의 버젼과 대응해야하고 더보기 링크의 기본값을 위해서 대표 게시판 아이디를 선택
        $menuItem = MenuItem::find(($boardIds) ?
            array_first($boardIds) :
            Board::where('type', BoardModule::getId())->first()->instance_id);

        //현재 사용하지않지만 기존버젼 대응을위해 살림
        $boardConfig = $configHandler->get($menuItem->id);

        $take = $widgetConfig['take'] ?? null;
        $recent_date = (int)$widgetConfig['recent_date'] ?? 0;
        $orderType = $widgetConfig['order_type'] ?? '';

        //아래 설정은 위젯에서 제공함
        $title = $widgetConfig['@attributes']['title'];
        $more = array_has($widgetConfig, 'more');

        /**
         * config 할수 있는것
         * 몇개, 게시판 아이디, 최근 몇일, 정렬 방법
         *
         * 2019.04.10 다중 선택 변환으로 division이 아닌 해당 document 테이블만 조회하도록 변경
         */
        $model = new Board();
        /** @var \Xpressengine\Database\DynamicQuery $query */

        //게시판, 카테고리 아이디 유무에 따라 각 쿼리를 분리
        if (count($boardIds) && count($categoryIds)) {
            $query = $model->where(function ($query) use ($boardIds, $categoryIds) {
                $query->whereIn('instance_id', $boardIds)
                    ->whereHas('boardCategory', function ($query) use ($categoryIds) {
                        $query->whereIn('item_id', $categoryIds);
                    });
            });
        } elseif (count($boardIds)) {
            $query = $model->newQuery();

            if (count($boardIds) === 1) {
                $query = Board::division($boardIds[0]);
            }

            $query->whereIn('instance_id', $boardIds);
        } elseif (count($categoryIds)) {
            $query = $model->whereHas('boardCategory', function ($query) use ($categoryIds) {
                $query->whereIn('item_id', $categoryIds);
            });
        } else {
            $query = $model->where('type', BoardModule::getId());
        }

        $query = $query->leftJoin(
            'board_gallery_thumbs',
            sprintf('%s.%s', $query->getQuery()->from, 'id'),
            '=',
            sprintf('%s.%s', 'board_gallery_thumbs', 'target_id')
        );

        switch (array_get($widgetConfig, 'noticeInList', array_get($widgetConfig, 'notice_type', 'withOutNotice'))) {
            case 'onlyNotice':
                $query->notice();
                break;

            case 'on':
            case 'withNotice':
                $query->visibleWithNotice();
                break;

            default:
                $query->visible();
                break;
        }

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
        } elseif ($orderType == 'random') {
            $query = $query->inRandomOrder();
        }

        if ($take) {
            $query = $query->take($take);
        }

        $list = $query->get();
        $list = $list->map(function ($item) use ($configHandler) {
            $item->boardConfig = $configHandler->get($item->instance_id);
            $item->thumb;

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
                'more' => $more,
                'boardIds' => $boardIds,
                'categoryIds' => $categoryIds,
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
            $categories = [];

            if ($config->get('category')) {
                $nodes = Category::find($config->get('categoryId'))->getTree()->getTreeNodes();

                $categories = $nodes->map(function ($item) {
                    return $this->getCategoryList($item);
                })->toArray();
            }

            $boardList[] = [
                'value' => $config->get('boardId'),
                'text' => $boardName,
                'categories' => $categories
            ];
        }
        return $boardList;
    }

    private function getCategoryList(CategoryItem $categoryItem)
    {
        $result = [
            'id' => $categoryItem->id,
            'name' => xe_trans($categoryItem->word),
            'children' => []
        ];

        $categoryItem->getChildren()->each(function (CategoryItem $categoryItem) use (&$result) {
            $result['children'][] = $this->getCategoryList($categoryItem);
        });

        return $result;
    }
}
