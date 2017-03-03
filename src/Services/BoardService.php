<?php
namespace Xpressengine\Plugins\Board\Services;

use Auth;
use Event;
use XeEditor;
use XeCaptcha;
use Xpressengine\Category\Models\Category;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Exceptions\CaptchaNotVerifiedException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundDocumentException;
use Xpressengine\Plugins\Board\Exceptions\SecretDocumentHttpException;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\IdentifyManager;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;
use Xpressengine\User\UserInterface;

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
     * @param Handler $handler
     * @param ConfigHandler $configHandler
     */
    public function __construct(Handler $handler, ConfigHandler $configHandler)
    {
        $this->handler = $handler;
        $this->configHandler = $configHandler;
    }

    /**
     * get notice list
     *
     * @param Request $request
     * @param ConfigEntity $config
     * @param $userId
     * @return mixed
     */
    public function getNoticeItems(Request $request, ConfigEntity $config, $userId)
    {
        $model = Board::division($config->get('boardId'));
        $query = $model->where('instanceId', $config->get('boardId'))
            ->visible()->orderBy('head', 'desc');

        if ($request->has('favorite') === true) {
            $query->leftJoin(
                'board_favorites',
                sprintf('%s.%s', $query->getQuery()->from, 'id'),
                '=',
                sprintf('%s.%s', 'board_favorites', 'targetId')
            );
            $query->where('board_favorites.userId', $userId);
        }

        Event::fire('xe.plugin.board.notice', [$query]);

        return $query->get();
    }

    /**
     * get article list
     *
     * @param Request $request
     * @param ConfigEntity $config
     * @param null $id
     * @return mixed
     */
    public function getItems(Request $request, ConfigEntity $config, $id = null)
    {
        /** @var Board $model */
        $model = Board::division($config->get('boardId'));
        $query = $model->where('instanceId', $config->get('boardId'))->visible();

        if ($config->get('category') === true) {
            $query->leftJoin(
                'board_category',
                sprintf('%s.%s', $query->getQuery()->from, 'id'),
                '=',
                sprintf('%s.%s', 'board_category', 'targetId')
            );
        }

        if ($request->has('favorite') === true) {
            $query->leftJoin(
                'board_favorites',
                sprintf('%s.%s', $query->getQuery()->from, 'id'),
                '=',
                sprintf('%s.%s', 'board_favorites', 'targetId')
            );
            $query->where('board_favorites.userId', Auth::user()->getId());
        }

        $this->handler->makeWhere($query, $request, $config);
        $this->handler->makeOrder($query, $request, $config);

        // eager loading favorite list
        $query->with(['favorite' => function($favoriteQuery) {
            $favoriteQuery->where('userId', Auth::user()->getId());
        }, 'slug', 'data']);

        Event::fire('xe.plugin.board.articles', [$query]);

        if ($id !== null) {
            $request->query->set('page', $this->handler->pageResolver($query, $config, $id));
        }

        $paginate = $query->paginate($config->get('perPage'))->appends($request->except('page'));

        return $paginate;
    }

    /**
     * get category item list
     *
     * @param ConfigEntity $config
     * @return array
     */
    public function getCategoryItems(ConfigEntity $config)
    {
        $items = [];
        if ($config->get('category') === true) {
            $categoryItems = Category::find($config->get('categoryId'))->items;
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
     * get category item
     *
     * @param ConfigEntity $config
     * @param Board $item
     * @return null
     */
    public function getCategoryItem(ConfigEntity $config, Board $item)
    {
        $showCategoryItem = null;
        if ($config->get('category') && $item->boardCategory) {
            $showCategoryItem = $item->boardCategory->categoryItem;
        }
        return $showCategoryItem;
    }

    /**
     * get dynamic field types
     *
     * @param ConfigEntity $config
     * @return array
     */
    public function getFieldTypes(ConfigEntity $config)
    {
        return (array)$this->configHandler->getDynamicFields($config);
    }

    /**
     * get article
     *
     * @param $id
     * @param UserInterface $user
     * @param ConfigEntity $config
     * @param bool $force
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
            } elseif ($user->getId() == $item->getAuthor()->getId()) {
                $visible = true;
            }
            if ($visible === false) {
                throw new SecretDocumentHttpException;
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
     * @param ConfigEntity $config
     */
    public function checkCaptcha(ConfigEntity $config)
    {
        if ($config->get('useCaptcha', false) === true) {
            if (XeCaptcha::verify() !== true) {
                throw new CaptchaNotVerifiedException;
            }
        }
    }

    /**
     * @param Request $request
     * @param UserInterface $user
     * @param ConfigEntity $config
     * @param IdentifyManager $identifyManager
     * @return Board
     */
    public function store(Request $request, UserInterface $user, ConfigEntity $config, IdentifyManager $identifyManager)
    {
        $this->checkCaptcha($config);

        // 암호 설정
        if ($request->has('certifyKey') === true) {
            $request->request->set('certifyKey', $identifyManager->hash($request->get('certifyKey')));
        }

        $inputs = $request->request->all();
        $inputs['instanceId'] = $config->get('boardId');
        $inputs['title'] = htmlspecialchars($request->originAll()['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
        $inputs['content'] = purify($request->originAll()['content']);

        /** @var \Xpressengine\Editor\AbstractEditor $editor */
        $editor = XeEditor::get($config->get('boardId'));
        $inputs['format'] = $editor->htmlable() ? Board::FORMAT_HTML : Board::FORMAT_NONE;

        // set file, tag
        $inputs['_files'] = array_get($inputs, $editor->getFileInputName(), []);
        $inputs['_hashTags'] = array_get($inputs, $editor->getTagInputName(), []);

        return $this->handler->add($inputs, $user, $config);
    }

    /**
     * update article
     *
     * @param Board $item
     * @param Request $request
     * @param UserInterface $user
     * @param ConfigEntity $config
     * @param IdentifyManager $identifyManager
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
        $oldCertifyKey = $item->certifyKey;
        $newCertifyKey = $request->get('certifyKey', '');
        if ($item->certifyKey != '' && $newCertifyKey == '') {
            $request->request->set('certifyKey', $item->certifyKey);
        } elseif ($item->certifyKey != '' && $newCertifyKey != '') {
            $request->request->set('certifyKey', $identifyManager->hash($newCertifyKey));
        }

        $inputs = $request->all();
        $inputs['title'] = htmlspecialchars($request->originAll()['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
        $inputs['content'] = purify($request->originAll()['content']);

        if ($request->get('status') == Board::STATUS_NOTICE) {
            $item->status = Board::STATUS_NOTICE;
        } else if ($request->get('status') != Board::STATUS_NOTICE && $item->status == Board::STATUS_NOTICE) {
            $item->status = Board::STATUS_PUBLIC;
        }

        /** @var \Xpressengine\Editor\AbstractEditor $editor */
        $editor = XeEditor::get($config->get('boardId'));
        $inputs['format'] = $editor->htmlable() ? Board::FORMAT_HTML : Board::FORMAT_NONE;

        // set file, tag
        $inputs['_files'] = array_get($inputs, $editor->getFileInputName(), []);
        $inputs['_hashTags'] = array_get($inputs, $editor->getTagInputName(), []);

        $item = $this->handler->put($item, $inputs, $config);

        // 비회원 비밀번호를 변경 한 경우 세션 변경
        if ($oldCertifyKey != '' && $oldCertifyKey != $item->certifyKey) {
            $identifyManager->destroy($item);
            $identifyManager->create($item);
        }

        return $item;
    }

    /**
     * destroy article
     *
     * @param Board $item
     * @param ConfigEntity $config
     * @param IdentifyManager $identifyManager
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

    public function hasItemPerm(Board $item, UserInterface $user, $force = false)
    {
        $perm = true;
        if ($force === true) {
            $perm = true;
        } elseif($item->userId == Auth::user()->getId()) {
            $perm = true;
        } elseif($item->userId == '' && Auth::user()->getId() == null) {
            $perm = true;
        }
        return $perm;
    }
}
