<?php
/**
 * BoardService
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
namespace Xpressengine\Plugins\Board\Services;

use Auth;
use Event;
use Illuminate\Support\Collection;
use XeEditor;
use XeCaptcha;
use Xpressengine\Category\Models\Category;
use Xpressengine\Category\Models\CategoryItem;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Document\Models\Document;
use Xpressengine\Editor\PurifierModules\EditorTool;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Exceptions\CaptchaNotVerifiedException;
use Xpressengine\Plugins\Board\Exceptions\GuestWrittenSecretDocumentException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundDocumentException;
use Xpressengine\Plugins\Board\Exceptions\SecretDocumentHttpException;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\IdentifyManager;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;
use Xpressengine\Support\PurifierModules\Html5;
use Xpressengine\User\Models\Guest;
use Xpressengine\User\UserInterface;
use Gate;
use Xpressengine\Permission\Instance;

/**
 * BoardService
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class BoardService
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var ConfigHandler
     */
    protected $configHandler;

    /**
     * BoardService constructor.
     * @param Handler       $handler       board handler
     * @param ConfigHandler $configHandler board config handler
     */
    public function __construct(Handler $handler, ConfigHandler $configHandler)
    {
        $this->handler = $handler;
        $this->configHandler = $configHandler;
    }

    /**
     * get notice list
     *
     * @param Request      $request request
     * @param ConfigEntity $config  board config entity
     * @param string       $userId  user id
     * @return mixed
     */
    public function getNoticeItems(Request $request, ConfigEntity $config, $userId)
    {
        $boardInstanceId = $config->get('boardId');

        $query = Board::division($boardInstanceId)
            ->newQuery()
            ->where('instance_id', $boardInstanceId)
            ->notice()
            ->orderBy('head', 'desc')
            ->when(
                $request->has('favorite') === true,
                function ($query) use ($userId) {
                    $fromTable =$query->getQuery()->from;

                    $query->leftJoin(
                        'board_favorites',
                        sprintf('%s.%s', $fromTable, 'id'),
                        '=',
                        sprintf('%s.%s', 'board_favorites', 'target_id')
                    );

                    $query->where('board_favorites.user_id', $userId);
                }
            )
            ->with([
                'slug', 'data', 'thumb', 'tags', 'user',
                'favoriteUsers' => function($favoriteUserQuery) {
                    $favoriteUserQuery->where('user.id', Auth::id());
                },
            ]);

        Event::fire('xe.plugin.board.notice', [$query, $request]);

        return $query->get();
    }

    /**
     * get article list
     *
     * @param Request      $request request
     * @param ConfigEntity $config  board config entity
     * @param string|null  $id      document id
     * @return mixed
     */
    public function getItems(Request $request, ConfigEntity $config, $id = null)
    {
        /** @var BoardPermissionHandler $boardPermission */
        $boardPermission = app('xe.board.permission');
        $boardInstanceId = $config->get('boardId');

        $query = Board::division($boardInstanceId)->newQuery();
        $query->where('instance_id', $boardInstanceId);

        $query->where(
            function($query) use ($config) {
                if ($config->get('noticeInList') === true) {
                    $query->visibleWithNotice();
                    return;
                }

                $query->visible();
            }
        );

        $query->when(
            $config->get('category') === true,
            function ($query) {
                $fromTable = $query->getQuery()->from;

                $query->leftJoin(
                    'board_category',
                    sprintf('%s.%s', $fromTable, 'id'),
                    '=',
                    sprintf('%s.%s', 'board_category', 'target_id')
                );
            }
        );

        $query->when(
            $config->get('favorite') === true,
            function ($query) {
                $fromTable = $query->getQuery()->from;

                $query->leftJoin(
                    'board_favorites',
                    sprintf('%s.%s', $fromTable, 'id'),
                    '=',
                    sprintf('%s.%s', 'board_favorites', 'target_id')
                );

                $query->where('board_favorites.user_id', Auth::user()->getId());
            }
        );

        $query->when(
            $config->get('useConsultation') === true,
            function ($query) use ($boardInstanceId, $boardPermission) {
                $permissionInstance = new Instance($boardPermission->name($boardInstanceId));

                $isManager = Gate::allows(
                    $boardPermission::ACTION_MANAGE,
                    $permissionInstance
                );

                $query->when(
                    $isManager === false,
                    function($query) {
                        $query->where('user_id', Auth::user()->getId());
                    }
                );
            }
        );

        $query->with([
            'slug', 'data', 'thumb', 'tags', 'user',
            'favoriteUsers' => function ($favoriteUserQuery) {
                $favoriteUserQuery->where('user.id', Auth::id());
            }
        ]);

        $this->handler->makeWhere($query, $request, $config);
        $this->handler->makeOrder($query, $request, $config);

        Event::fire('xe.plugin.board.articles', [$query, $request]);

        if ($id !== null) {
            $board = Board::division($boardInstanceId)->newQuery()->find($id);
            $queryPage = $this->handler->pageResolver($query, $config, $id);

            if ($board->status === Document::STATUS_NOTICE) {
                $queryPage = 1;
            }

            $request->query->set('page', $queryPage);
        }

        return $query->paginate($config->get('perPage'))->appends($request->except('page'));
    }

    /**
     * 신규 스킨 상세보기 페이지에서 사용할 동일 게시판의 최근 글 반
     *
     * @param ConfigEntity $config        board config entity환
     * @param string       $currentItemId current show board item id
     *
     * @return Collection
     */
    public function getBoardMoreItems(ConfigEntity $config, $currentItemId)
    {
        $boardInstanceId = $config->get('boardId');

        $query = Board::division($boardInstanceId)
            ->newQuery()
            ->visible()
            ->orderBy('head', 'desc')
            ->with(['slug'])
            ->where([
                ['instance_id', '=', $boardInstanceId],
                ['id', '<>', $currentItemId],
            ])
            ->when(
                $config->has('category') === true,
                function ($query) {
                    $fromTable =$query->getQuery()->from;

                    $query->leftJoin(
                        'board_category',
                        sprintf('%s.%s', $fromTable, 'id'),
                        '=',
                        sprintf('%s.%s', '', 'target_id')
                    );
                }
            );

        Event::fire('xe.plugin.board.moreItems', [$query, $config]);
        
        return $query->take(8)->get();
    }

    /**
     * get category item list
     *
     * @param ConfigEntity $config board config entity
     * @return array
     */
    public function getCategoryItems(ConfigEntity $config)
    {
        $items = [];
        if ($config->get('category') === true) {
            $categoryItems = CategoryItem::where('category_id', $config->get('categoryId'))
                ->orderBy('ordering')->get();
            foreach ($categoryItems as $categoryItem) {
                $items[] = [
                    'value' => $categoryItem->id,
                    'text' => $categoryItem->word,
                ];
            }
        }

        return $items;
    }

    /**
     * get category item tree
     *
     * @param ConfigEntity $config board config entity
     * @return array
     */
    public function getCategoryItemsTree(ConfigEntity $config)
    {
        $items = [];
        if ($config->get('category') === true) {
            $categoryItems = CategoryItem::where('category_id', $config->get('categoryId'))
                ->where('parent_id', null)
                ->orderBy('ordering')->get();

            foreach ($categoryItems as $categoryItem) {
                $categoryItemData = [
                    'value' => $categoryItem->id,
                    'text' => xe_trans($categoryItem->word),
                    'children' => $this->getCategoryItemChildrenData($categoryItem)
                ];

                $items[] = $categoryItemData;
            }

        }

        return $items;
    }

    /**
     * get category item data
     *
     * @param CategoryItem $categoryItem target category
     *
     * @return array
     */
    private function getCategoryItemChildrenData(CategoryItem $categoryItem)
    {
        $children = $categoryItem->getChildren();

        if ($children->isEmpty() === true) {
            return [];
        }

        $childrenData = [];
        foreach ($children as $child) {
            $childrenData[] = [
                'value' => $child->id,
                'text' => xe_trans($child->word),
                'children' => $this->getCategoryItemChildrenData($child)
            ];
        }

        return $childrenData;
    }

    /**
     * get category item
     *
     * @param ConfigEntity $config board config entity
     * @param Board        $item   board model
     * @return null
     */
    public function getCategoryItem(ConfigEntity $config, Board $item)
    {
        $showCategoryItem = null;
        if ($config->get('category') && $item->boardCategory) {
            $showCategoryItem = $item->boardCategory->category_item;
        }
        return $showCategoryItem;
    }

    /**
     * get dynamic field types
     *
     * @param ConfigEntity $config board config entity
     * @return array
     */
    public function getFieldTypes(ConfigEntity $config)
    {
        return (array)$this->configHandler->getDynamicFields($config);
    }

    /**
     * get search option array
     *
     * @param Request $request request
     * @return array
     */
    public function getSearchOptions(Request $request)
    {
        $searchType = ['title_pure_content' => 'board::titleAndContent',
                    'title_content' => 'board::title',
                    'writer' => 'board::writer',
                    'category_item_id' => 'xe::category',
                    'start_created_at' => 'board::startDate',
                    'end_created_at' => 'board::endDate',
                    'searchTag' => 'xe::tag'];

        $searchOption = [];

        foreach ($searchType as $type => $name) {
            $value = $request->get($type);

            if ($value != null) {
                if ($type == 'category_item_id') {
                    $category = CategoryItem::where('id', $value)->get()->first();

                    $searchOption[xe_trans($name)] = xe_trans($category->word);
                } else {
                    $searchOption[xe_trans($name)] = $value;
                }
            }
        }

        return $searchOption;
    }

    /**
     * get article
     *
     * @param string        $id     document id
     * @param UserInterface $user   user
     * @param ConfigEntity  $config board config entity
     * @param bool          $force  force
     * @return Board
     */
    public function getItem($id, UserInterface $user, ConfigEntity $config, $force = false)
    {
        /** @var Board $item */
        $item = Board::division($config->get('boardId'))->find($id);

        if ($item === null) {
            throw new NotFoundDocumentException;
        }

        $visible = false;
        if ($item->display == Board::DISPLAY_VISIBLE) {
            $visible = true;
        }
        if ($item->display == Board::DISPLAY_SECRET) {
            if ($force === true) {
                $visible = true;
            } elseif ($user instanceof Guest && $item->isGuest()) {
                $identifyManager = app('xe.board.identify');

                if ($identifyManager->identified($item) === true) {
                    $visible = true;
                } else {
                    throw new GuestWrittenSecretDocumentException;
                }
            } elseif ($user->getId() == $item->getAuthor()->getId()) {
                $visible = true;
            }

            if ($visible === false) {
                throw new SecretDocumentHttpException;
            }
        }

        if ($item->approved != Board::APPROVED_APPROVED) {
            if ($force === true) {
                $visible = true;
            } else {
                if ($user->getId() == $item->user_id) {
                    $visible = true;
                }
            }
        }

        if ($item->status == Board::STATUS_TRASH) {
            if ($force === true) {
                $visible = true;
            }
        }

        if ($visible !== true) {
            throw new AccessDeniedHttpException;
        }

        return $item;
    }

    /**
     * check captcha configuration
     *
     * @param ConfigEntity $config board config entity
     * @return void
     */
    public function checkCaptcha(ConfigEntity $config)
    {
        if ($config->get('useCaptcha', false) === true) {
            if (app('xe.captcha')->verify() !== true) {
                throw new CaptchaNotVerifiedException;
            }
        }
    }

    /**
     * store board item
     *
     * @param Request         $request         request
     * @param UserInterface   $user            user
     * @param ConfigEntity    $config          board config entity
     * @param IdentifyManager $identifyManager identify manager
     * @return Board
     */
    public function store(Request $request, UserInterface $user, ConfigEntity $config, IdentifyManager $identifyManager)
    {
        $this->checkCaptcha($config);

        // 암호 설정
        if ($request->has('certify_key') === true) {
            $request->request->set('certify_key', $identifyManager->hash($request->get('certify_key')));
        }

        $inputs = $request->request->all();
        $inputs['instance_id'] = $config->get('boardId');

        /** @var \Xpressengine\Editor\AbstractEditor $editor */
        $editor = XeEditor::get($config->get('boardId'));
        $inputs['format'] = $editor->htmlable() ? Board::FORMAT_HTML : Board::FORMAT_NONE;

        // set file, tag
        $inputs['_files'] = array_get($inputs, $editor->getFileInputName(), []);
        $inputs['_hashTags'] = array_get($inputs, $editor->getTagInputName(), []);
        $inputs['_coverId'] = array_get($inputs, $editor->getCoverInputName(), []);

        return $this->handler->add($inputs, $user, $config);
    }

    /**
     * update article
     *
     * @param Board           $item            board model item
     * @param Request         $request         request
     * @param UserInterface   $user            user
     * @param ConfigEntity    $config          board config entity
     * @param IdentifyManager $identifyManager identify manager
     * @return Board
     */
    public function update(
        Board $item,
        Request $request,
        UserInterface $user,
        ConfigEntity $config,
        IdentifyManager $identifyManager
    ) {
        // 암호 설정
        $oldCertifyKey = $item->certify_key;
        $newCertifyKey = $request->get('certify_key', '');
        if ($item->certify_key != '' && $newCertifyKey == '') {
            $request->request->set('certify_key', $item->certify_key);
        } elseif ($item->certify_key != '' && $newCertifyKey != '') {
            $request->request->set('certify_key', $identifyManager->hash($newCertifyKey));
        }

        if ($request->get('status') == Board::STATUS_NOTICE) {
            $item->status = Board::STATUS_NOTICE;
        } elseif ($request->get('status') != Board::STATUS_NOTICE && $item->status == Board::STATUS_NOTICE) {
            $item->status = Board::STATUS_PUBLIC;
        }

        if ($request->get('display') == Board::DISPLAY_SECRET) {
            $item->display = Board::DISPLAY_SECRET;
        } else {
            $item->display = Board::DISPLAY_VISIBLE;
        }

        $inputs = $request->all();

        /** @var \Xpressengine\Editor\AbstractEditor $editor */
        $editor = XeEditor::get($config->get('boardId'));
        $inputs['format'] = $editor->htmlable() ? Board::FORMAT_HTML : Board::FORMAT_NONE;

        // set file, tag
        $inputs['_files'] = array_get($inputs, $editor->getFileInputName(), []);
        $inputs['_hashTags'] = array_get($inputs, $editor->getTagInputName(), []);
        $inputs['_coverId'] = array_get($inputs, $editor->getCoverInputName(), []);
        $item = $this->handler->put($item, $inputs, $config);

        // 비회원 비밀번호를 변경 한 경우 세션 변경
        if ($oldCertifyKey != '' && $oldCertifyKey != $item->certify_key) {
            $identifyManager->destroy($item);
            $identifyManager->create($item);
        }

        return $item;
    }

    /**
     * destroy article
     *
     * @param Board           $item            board model item
     * @param ConfigEntity    $config          board config entity
     * @param IdentifyManager $identifyManager identify manager
     * @return void
     */
    public function destroy(Board $item, ConfigEntity $config, IdentifyManager $identifyManager)
    {
        if ($config->get('deleteToTrash') === true) {
            $this->handler->trash($item, $config);
        } else {
            $this->handler->remove($item, $config);
        }
        $identifyManager->destroy($item);
    }

    /**
     * has article permission
     *
     * @param Board           $item            board model item
     * @param UserInterface   $user            user
     * @param IdentifyManager $identifyManager identify manager
     * @param bool            $force           force
     *
     * @return bool
     */
    public function hasItemPerm(Board $item, UserInterface $user, IdentifyManager $identifyManager, $force = false)
    {
        $perm = false;
        if ($force === true) {
            $perm = true;
        } elseif ($item->user_id == $user->getId()) {
            $perm = true;
        } elseif ($item->user_id == '' && $user->getId() === null &&
            $identifyManager->identified($item) === true) {
            $perm = true;
        }
        return $perm;
    }

    /**
     * get category item tree
     *
     * @param ConfigEntity $config board config entity
     * @return array
     */
    public function getTitleHeadItems(ConfigEntity $config)
    {
        $items = [];
        if ($config->get('useTitleHead') === true) {
            $strTitleHeadItem = $config->get('titleHeadItem');
            if ($strTitleHeadItem == null || $strTitleHeadItem == '') {
                return $items;
            }

            $arrTitleHeadItem = explode(',', $strTitleHeadItem);
            foreach ($arrTitleHeadItem as $titleHeadItem) {
                $items[] = [
                    'value' => $titleHeadItem,
                    'text' => $titleHeadItem,
                ];
            }
        }

        return $items;
    }
}
