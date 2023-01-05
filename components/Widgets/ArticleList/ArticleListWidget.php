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
use Illuminate\Support\Collection;
use View;
use Xpressengine\Category\CategoryHandler;
use Xpressengine\Category\Models\Category;
use Xpressengine\Category\Models\CategoryItem;
use Xpressengine\Config\ConfigEntity;
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
     * @var CategoryHandler
     */
    protected $categoryHandler;

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
        $this->categoryHandler = app(CategoryHandler::class);
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

        // board config
        $take = Arr::get($widgetConfig, 'take');
        $recent_date = (int)Arr::get($widgetConfig, 'recent_date', 0);
        $orderType = Arr::get($widgetConfig, 'order_type', '');
        $noticeInList = array_get($widgetConfig, 'noticeInList', array_get($widgetConfig, 'notice_type', 'withOutNotice'));

        // more
        $more = array_has($widgetConfig, 'more');
        $moreMenuItem = null;
        $moreBoardConfig = null;
        $urlMore = null;

        // pagination
        $pagination = array_has($widgetConfig, 'pagination');
        $pageName = Arr::get($widgetConfig, 'page_name');

        if (array_has($widgetConfig, 'board_id') === false) {
            $widgetConfig['board_id']['item'] = [];
        }

        $selectedCategories = (is_array($widgetConfig['board_id']) === true) ?
            $widgetConfig['board_id']['item'] :
            [$widgetConfig['board_id']];

        $selectedCategories = collect($selectedCategories);
        $selectedBoardIds = $this->getSelectedBoardIds($selectedCategories);
        $selectedCategoryItemIds = $this->getSelectedCategoryItemIds($selectedCategories);

        $query = Board::query();

        if ($selectedCategoryItemIds->isEmpty() === true && $selectedBoardIds->count() == 1) {
            $query = Board::division($selectedBoardIds->first())->newQuery();
        }

        $query->where('type', BoardModule::getId());

        $query->where(function ($query) use ($selectedBoardIds, $selectedCategoryItemIds) {
            $query->when(
                $selectedBoardIds->isNotEmpty() === true,
                function ($query) use ($selectedBoardIds) {
                    $query->whereIn('instance_id', $selectedBoardIds);
                }
            );

            $query->when(
                $selectedCategoryItemIds->isNotEmpty() === true,
                function ($query) use ($selectedCategoryItemIds) {
                    $query->orWhereHas('boardCategory',
                        function ($query) use ($selectedCategoryItemIds) {
                            $query->whereIn('item_id', $selectedCategoryItemIds);
                        }
                    );
                }
            );
        });

        // display only my posts
        if (array_has($widgetConfig, 'display_my_posts')) {
            $targetUserId = auth()->id() ?: '';
            $query->where('user_id', $targetUserId);
        }

        // display only my favorite posts
        if (array_has($widgetConfig, 'display_favorite_posts')) {
            $targetUserId = auth()->id() ?: '';
            $query->whereHas('favoriteUsers', function ($query) use ($targetUserId) {
                $query->where('id', $targetUserId);
            });
        }

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

        if(!$pagination) {
            $query->when(
                $take,
                function ($query, $take) {
                    $query->take($take);
                }
            );
        }

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
                    $orderType === 'read_count',
                    function ($query) {
                        $query->orderBy('read_count', 'desc')->orderBy('head', 'desc');
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

        $query->with(['thumb', 'slug', 'boardCategory', 'boardCategory.categoryItem']);
        if($pagination) {
            $boardList = $query->paginate($take, ['*'], empty($pageName) ? 'page' : $pageName);
        } else {
            $boardList = $query->get();
        }

        $boardList->transform(function ($item) {
            $item->boardConfig = $this->boardConfigHandler->get($item->instance_id);
            $thumb = $item->thumb;

            if ($thumb !== null) {
                $item->setAttribute('target_id', $thumb->target_id);
                $item->setAttribute('board_thumbnail_file_id', $thumb->board_thumbnail_file_id);
                $item->setAttribute('board_thumbnail_external_path', $thumb->board_thumbnail_external_path);
                $item->setAttribute('board_thumbnail_path', $thumb->board_thumbnail_path);
            }

            return $item;
        });

        // more 더보기 처리
        if ($more === true) {
            $moreMenuItem = $this->getMoreMenuItem(
                $selectedBoardIds,
                $selectedCategoryItemIds,
                $pagination ? $boardList->getCollection() : $boardList
            );

            $moreBoardConfig = $this->boardConfigHandler->get($moreMenuItem->id);
            $moreBoardCategoryId = $moreBoardConfig->get('categoryId');
            $moreCategoryItems = collect([]);

            $urlMore = instance_route('index', [], $moreMenuItem->id);
            $categoryItemId = $selectedCategoryItemIds->first();

            if (is_null($moreBoardCategoryId) === false) {
                $moreCategoryItems = $this->categoryHandler->items()->where('category_id', $moreBoardCategoryId)->get();
            }

            if (is_null($categoryItemId) === false) {
                if ($moreCategoryItems->contains('id', $categoryItemId) === true) {
                    $urlMore = instance_route('index', ['category_item_id' => $categoryItemId], $moreMenuItem->id);
                } else {
                    $more = false;
                }
            }
        }

        return $this->renderSkin(
            [
                'list' => $boardList,
                'boardConfig' => $moreBoardConfig,
                'menuItem' => $moreMenuItem,
                'widgetConfig' => $widgetConfig,
                'urlHandler' => $this->boardUrlHandler,
                'title' => $title,
                'more' => $more,
                'pagination' => $pagination,
                'boardIds' => $selectedBoardIds->toArray(),
                'categoryIds' => $selectedCategoryItemIds->toArray(),
                'urlMore' => $urlMore
            ]
        );
    }

    /**
     * Get Selected Board Ids
     *
     * @param Collection $selectedCategories
     * @return Collection
     */
    protected function getSelectedBoardIds(Collection $selectedCategories)
    {
        return $selectedCategories->filter(
            function ($item) {
                return starts_with($item, 'category.') === false;
            }
        );
    }

    /**
     * Get Selected Category Item Ids
     *
     * @param Collection $selectedCategories
     * @return Collection
     */
    protected function getSelectedCategoryItemIds(Collection $selectedCategories)
    {
        return $selectedCategories
            ->filter(
                function ($item) {
                    return starts_with($item, 'category.') === true;
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
    }

    /**
     * Get More Menu Item
     *
     * @param Collection $selectedBoardIds
     * @param Collection $selectedCategoryItemIds
     * @param Collection $boardList
     * @return MenuItem
     */
    protected function getMoreMenuItem(
        Collection $selectedBoardIds,
        Collection $selectedCategoryItemIds,
        Collection $boardList
    )
    {
        $moreMenuItemId = $selectedBoardIds->first();

        if ($moreMenuItemId === null) {
            if ($selectedCategoryItemIds->isNotEmpty() === true) {
                $categoryItemId = $selectedCategoryItemIds->first();

                $board = $boardList->first(
                    function ($board) use ($categoryItemId) {
                        return $board->boardCategory->item_id === $categoryItemId;
                    }
                );

                $moreMenuItemId = $board->instance_id;
            } else {
                $moreMenuItemId = Board::where('type', BoardModule::getId())->first()->instance_id;
            }
        }

        return MenuItem::find($moreMenuItemId);
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
        return View::make(sprintf('%s/views/setting', static::$path), [
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

        /** @var ConfigEntity $config */
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
