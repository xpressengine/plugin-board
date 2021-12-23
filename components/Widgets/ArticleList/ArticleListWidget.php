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
use Illuminate\Support\Arr;
use View;
use Xpressengine\Category\Models\Category;
use Xpressengine\Category\Models\CategoryItem;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Menu\Repositories\MenuItemRepository;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\UrlHandler;
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
     * @var Handler;
     */
    protected $boardHandler;

    /**
     * @var ConfigHandler
     */
    protected $boardConfigHandler;

    /**
     * @var UrlHandler
     */
    protected $boardUrlHandler;

    /**
     * Article Lis tWidget __construct
     *
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        parent::__construct($config);

        $this->boardHandler = app('xe.board.handler');
        $this->boardConfigHandler = app('xe.board.config');
        $this->boardUrlHandler = app('xe.board.url');
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        $widgetConfig = $this->setting();

        $title = $widgetConfig['@attributes']['title'];
        $more = array_has($widgetConfig, 'more');

        $take = Arr::get($widgetConfig, 'take');
        $recent_date = (int)Arr::get($widgetConfig, 'recent_date', 0);
        $orderType = Arr::get($widgetConfig, 'order_type', '');
        $noticeInList = array_get($widgetConfig, 'noticeInList', array_get($widgetConfig, 'notice_type', 'withOutNotice'));

        if (array_has($widgetConfig, 'board_id') === false) {
            $widgetConfig['board_id']['item'] = [];
        }

        $selectedCategories = (is_array($widgetConfig['board_id']) === true) ?
            $widgetConfig['board_id']['item'] :
            [$widgetConfig['board_id']];

        $selectedCategories = collect($selectedCategories);

        $selectedBoardIds = $selectedCategories->filter(
            function ($item) {
                return mb_substr($item, 0, 9) != 'category.';
            }
        );

        $selectedCategoryIds = $selectedCategories
            ->filter(
                function ($item) {
                    return mb_substr($item, 0, 9) == 'category.';
                }
            )
            ->transform(function (string $id) {
                $item = CategoryItem::find(mb_substr($id, 9));

                if ($item === null) {
                    return [];
                }

                return $item->getDescendantTree(true)->getNodes()->pluck('id');
            })
            ->flatten();

        $query = Board::query();

        if ($selectedCategoryIds->isEmpty() === true && $selectedBoardIds->count() == 1) {
            $query = Board::division($selectedCategoryIds->first())->newQuery();
        }

        $query->where('type', BoardModule::getId());

        $query->when(
            $selectedBoardIds->isNotEmpty(),
            function ($query) use ($selectedBoardIds) {
                $query->whereIn('instance_id', $selectedBoardIds);
            }
        );

        $query->when(
            $selectedCategoryIds->isNotEmpty(),
            function ($query) use ($selectedCategoryIds) {
                $query->whereHas('boardCategory',
                    function ($query) use ($selectedCategoryIds) {
                        $query->whereIn('item_id', $selectedCategoryIds);
                    }
                );
            }
        );

        switch ($noticeInList) {
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

        $query->when(
            $take,
            function ($query, $take) {
                $query->take($take);
            }
        );

        $query->when(
            $recent_date !== 0,
            function ($query) use ($recent_date) {
                $current = Carbon::now();
                $after = $current->addDay(-1 * $recent_date)->startOfDay();
                $before = $current->addDay($recent_date)->endOfDay();

                $query->where(
                    ['created_at', '>=', $after],
                    ['created_at', '<=', $before]
                );
            }
        );

        $query->when(
            $orderType,
            function ($query, $orderType) {
                $query->when(
                    $orderType === '',
                    function ($query) {
                        $query->orderBy('head', 'desc');
                    }
                );

                $query->when(
                    $orderType === 'assent_count',
                    function ($query) {
                        $query->orderBy('assent_count', 'desc')->orderBy('head', 'desc');
                    }
                );

                $query->when(
                    $orderType === 'recentlyCreated',
                    function ($query) {
                        $query->orderBy(Board::CREATED_AT, 'desc')->orderBy('head', 'desc');
                    }
                );

                $query->when(
                    $orderType === 'recentlyUpdated',
                    function ($query) {
                        $query->orderBy(Board::UPDATED_AT, 'desc')->orderBy('head', 'desc');
                    }
                );

                $query->when(
                    $orderType === 'random',
                    function ($query) {
                        $query->inRandomOrder();
                    }
                );
            }
        );

        $list = $query->with(['thumb', 'slug'])->get();

        $list = $list->map(function ($item) {
            $item->boardConfig = $this->boardConfigHandler->get($item->instance_id);
            return $item;
        });

        $moreMenuItem = MenuItem::find($selectedBoardIds->isNotEmpty() ?
            $selectedBoardIds->first() :
            Board::where('type', BoardModule::getId())->first()->instance_id
        );

        $moreBoardConfig = $this->boardConfigHandler->get($moreMenuItem->id);

        return $this->renderSkin(
            [
                'list' => $list,
                'boardConfig' => $moreBoardConfig,
                'menuItem' => $moreMenuItem,
                'widgetConfig' => $widgetConfig,
                'urlHandler' => new UrlHandler($moreBoardConfig),
                'title' => $title,
                'more' => $more,
                'boardIds' => $selectedBoardIds->toArray(),
                'categoryIds' => $selectedCategoryIds->toArray(),
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
                $menuItem = app(MenuItemRepository::class)->fetchIn([$config->get('boardId')], [])->first();
                $boardName = $menuItem->title ?? $config->get('boardId');
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

    /**
     * get category list
     *
     * @param CategoryItem $categoryItem
     * @return array
     */
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
