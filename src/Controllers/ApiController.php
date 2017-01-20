<?php
namespace Xpressengine\Plugins\Board\Controllers;

use XeDocument;
use XePresenter;
use XeFrontend;
use XeEditor;
use XeStorage;
use XeTag;
use Auth;
use Gate;
use Event;
use App\Http\Controllers\Controller;
use Xpressengine\Category\Models\Category;
use Xpressengine\Category\Models\CategoryItem;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Counter\Exceptions\GuestNotSupportException;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Permission\Instance;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Exceptions\CaptchaNotVerifiedException;
use Xpressengine\Plugins\Board\Exceptions\HaveNoWritePermissionHttpException;
use Xpressengine\Plugins\Board\Exceptions\InvalidRequestException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundDocumentException;
use Xpressengine\Plugins\Board\Exceptions\NotMatchedCertifyKeyException;
use Xpressengine\Plugins\Board\Exceptions\RequiredValueHttpException;
use Xpressengine\Plugins\Board\Exceptions\SecretDocumentHttpException;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\IdentifyManager;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Modules\Board as BoardModule;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Plugins\Board\Purifier;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Validator;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;
use Xpressengine\User\Models\User;
use Xpressengine\User\UserInterface;


class ApiController extends Controller
{
    /**
     * @var string
     */
    protected $instanceId;

    /**
     * @var Handler
     */
    public $handler;

    /**
     * @var ConfigHandler
     */
    public $configHandler;

    /**
     * @var UrlHandler
     */
    public $urlHandler;

    /**
     * @var ConfigEntity
     */
    public $config;

    /**
     * @var bool
     */
    public $isManager = false;

    public function __construct(
        Handler $handler,
        ConfigHandler $configHandler,
        UrlHandler $urlHandler,
        BoardPermissionHandler $boardPermission
    ) {
        $instanceConfig = InstanceConfig::instance();
        $this->instanceId = $instanceConfig->getInstanceId();

        $this->handler = $handler;
        $this->configHandler = $configHandler;
        $this->urlHandler = $urlHandler;

        $this->config = $configHandler->get($this->instanceId);
        if ($this->config !== null) {
            $urlHandler->setConfig($this->config);

            $this->isManager = false;
            if (Gate::allows(
                BoardPermissionHandler::ACTION_MANAGE,
                new Instance($boardPermission->name($this->instanceId)))
            ) {
                $this->isManager = true;
            };
        }

        // set Skin
        XePresenter::setSkinTargetId(BoardModule::getId());
        XePresenter::share('handler', $handler);
        XePresenter::share('configHandler', $configHandler);
        XePresenter::share('urlHandler', $urlHandler);
        XePresenter::share('isManager', $this->isManager);
        XePresenter::share('instanceId', $this->instanceId);
        XePresenter::share('config', $this->config);

    }

    public function notice($instanceId)
    {

    }

    public function articles(Request $request, BoardPermissionHandler $boardPermission)
    {
        $id = $request->get('current');

        if (Gate::denies(
            BoardPermissionHandler::ACTION_LIST,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        $query = $this->handler->getModel($this->config)
            ->where('instanceId', $this->instanceId)->visible();

        if ($this->config->get('category') === true) {
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

        $this->handler->makeWhere($query, $request, $this->config);
        $this->handler->makeOrder($query, $request, $this->config);

        // eager loading favorite list
        $query->with(['favorite' => function($favoriteQuery) {
            $favoriteQuery->where('userId', Auth::user()->getId());
        }, 'slug', 'data']);

        Event::fire('xe.plugin.board.articles', [$query]);

        if ($id !== null) {
            $request->query->set('page', $this->handler->pageResolver($query, $this->config, $id));
        }

        $paginate = $query->paginate($this->config->get('perPage'))->appends($request->except('page'));

        $fieldTypes = (array)$this->configHandler->getDynamicFields($this->config);

        $categories = [];
        if ($this->config->get('category') === true) {
            $categoryItems = Category::find($this->config->get('categoryId'))->items;
            foreach ($categoryItems as $categoryItem) {
                $categories[] = [
                    'value' => $categoryItem->id,
                    'text' => xe_trans($categoryItem->word),
                ];
            }
        }

        return XePresenter::makeApi([
            'paginate' => $paginate,
            'categories' => $categories,
            'fieldTypes' => $fieldTypes,
        ]);
    }

    public function article(Request $request, BoardPermissionHandler $boardPermission, $menuUrl, $id)
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_READ,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        if (Gate::denies(
            BoardPermissionHandler::ACTION_READ,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        /** @var UserInterface $user */
        $user = Auth::user();
        /** @var Board $item */
        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        $visible = false;
        if ($item->display == Document::DISPLAY_VISIBLE) {
            $visible = true;
        }
        if ($item->display == Document::DISPLAY_SECRET) {
            if ($this->isManager == true) {
                $visible = true;
            } elseif ($user->getId() == $item->getAuthor()->getId()) {
                $visible = true;
            }
            if ($visible === false) {
                throw new SecretDocumentHttpException;
            }
        }

        if ($visible === true) {
            $this->handler->incrementReadCount($item, $user);
        }

        $showCategoryItem = null;
        if ($this->config->get('category')) {
            $boardCategory = $item->boardCategory;
            if ($boardCategory) {
                $showCategoryItem = $boardCategory->categoryItem;
            }
        }

        $categories = [];
        if ($this->config->get('category') === true) {
            $categoryItems = Category::find($this->config->get('categoryId'))->items;
            foreach ($categoryItems as $categoryItem) {
                $categories[] = [
                    'value' => $categoryItem->id,
                    'text' => xe_trans($categoryItem->word),
                ];
            }
        }

        return XePresenter::makeApi([
            'item' => $item,
            'visible' => $visible,
            'showCategoryItem' => $showCategoryItem,
            'categories' => $categories,
            'links' => [
                'edit' => $this->urlHandler->get('api.edit', ['id' => $item->id]),
                'update' => $this->urlHandler->get('api.update', ['id' => $item->id]),
                'delete' => $this->urlHandler->get('api.delete', ['id' => $item->id]),
            ],
        ]);
    }

    public function create(Request $request, Validator $validator, BoardPermissionHandler $boardPermission)
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_CREATE,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        $head = '';

        $categories = [];
        if ($this->config->get('category') === true) {
            $categoryItems = Category::find($this->config->get('categoryId'))->items;
            foreach ($categoryItems as $categoryItem) {
                $categories[] = [
                    'value' => $categoryItem->id,
                    'text' => $categoryItem->word,
                ];
            }
        }

        /** @var UserInterface $user */
        $user = Auth::user();
        $rules = $validator->getCreateRule($user, $this->config);

        return XePresenter::makeApi([
            'head' => $head,
            'categories' => $categories,
            'rules' => $rules,
            'links' => [
                'create' => $this->urlHandler->get('api.create'),
            ],
        ]);
    }

    public function store(
        Request $request,
        Validator $validator,
        BoardPermissionHandler $boardPermission,
        IdentifyManager $identifyManager
    ) {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_CREATE,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

//        $this->checkCaptcha();

        $user = Auth::user();

        $this->validate($request, $validator->getCreateRule($user, $this->config));

        $inputs = $request->all();
        $inputs['instanceId'] = $this->instanceId;
        $inputs['title'] = htmlspecialchars($request->originAll()['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);

        $inputs['content'] = purify($request->originAll()['content']);

        if ($request->get('status') == Board::STATUS_NOTICE && $this->isManager === false) {
            throw new HaveNoWritePermissionHttpException(['name' => xe_trans('xe::notice')]);
        }

        // 암호 설정
        if (empty($inputs['certifyKey']) === false) {
            $inputs['certifyKey'] = $identifyManager->hash($inputs['certifyKey']);
        }

        /** @var \Xpressengine\Editor\AbstractEditor $editor */
        $editor = XeEditor::get($this->instanceId);
        $inputs['format'] = $editor->htmlable() ? Board::FORMAT_HTML : Board::FORMAT_NONE;

        // set file, tag
        $inputs['_files'] = array_get($inputs, $editor->getFileInputName(), []);
        $inputs['_hashTags'] = array_get($inputs, $editor->getTagInputName(), []);

        $board = $this->handler->add($inputs, $user, $this->config);

        return XePresenter::makeApi([
            'item' => $board,
            'links' => [
                'article' => $this->urlHandler->get('api.article', ['id' => $board->id]),
            ],
        ]);
    }

    public function hasSlug(Request $request)
    {
        $slug = BoardSlug::convert('', $request->get('slug'));
        $slug = BoardSlug::make($slug, $request->get('id'));

        return XePresenter::makeApi([
            'slug' => $slug,
        ]);
    }

    public function edit(
        Request $request,
        Validator $validator,
        BoardPermissionHandler $boardPermission,
        IdentifyManager $identifyManager,
        $menuUrl,
        $id
    ) {
        $user = Auth::user();

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        if ($item === null) {
            throw new NotFoundDocumentException;
        }

        // 비회원이 작성 한 글일 때 인증페이지로 이동
        if (
            $this->isManager !== true &&
            $item->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            $user->getRating() != 'super'
        ) {
            return $this->guestId($menuUrl, $item->id);
        }

        // 접근 권한 확인
        if ($this->isManager !== true && $item->userId !== $user->getId()) {
            throw new AccessDeniedHttpException;
        }

        $categories = [];
        if ($this->config->get('category') === true) {
            $categoryItems = Category::find($this->config->get('categoryId'))->items;
            foreach ($categoryItems as $categoryItem) {
                $categories[] = [
                    'value' => $categoryItem->id,
                    'text' => $categoryItem->word,
                ];
            }
        }

        /** @var \Xpressengine\Plugins\Board\Validator $validator */
        $validator = app('xe.board.validator');
        $rules = $validator->getEditRule($user, $this->config);

        $parent = null;

        return XePresenter::makeApi([
            'config' => $this->config,
            'item' => $item,
            'parent' => $parent,
            'categories' => $categories,
            'rules' => $rules,
        ]);
    }

    public function update(
        Request $request,
        Validator $validator,
        BoardPermissionHandler $boardPermission,
        IdentifyManager $identifyManager,
        $menuUrl
    ) {
        $user = Auth::user();
        $id = $request->get('id');

        if ($id === null) {
            throw new RequiredValueHttpException(['key' => 'id']);
        }

        // 글 수정 시 게시판 설정이 아닌 글의 상태에 따른 처리가 되어야 한다.
        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        // 비회원이 작성 한 글 인증
        if (
            $this->isManager !== true &&
            $item->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            $user->getRating() != 'super'
        ) {
            return $this->guestId($menuUrl, $item->id, $this->urlHandler->get('edit', ['id' => $item->id]));
        }

        // 접근 권한 확인
        if ($this->isManager !== true && $item->userId !== $user->getId()) {
            throw new AccessDeniedHttpException;
        }

        $rules = $validator->getEditRule($user, $this->config);
        $this->validate($request, $rules);

        $inputs = $request->all();
        $inputs['title'] = htmlspecialchars($request->originAll()['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
        $inputs['content'] = purify($request->originAll()['content']);

        if ($request->get('status') == Board::STATUS_NOTICE && $this->isManager === false) {
            throw new HaveNoWritePermissionHttpException(['name' => xe_trans('xe::notice')]);
        }

        if ($request->get('status') == Board::STATUS_NOTICE) {
            $item->status = Board::STATUS_NOTICE;
        } else if ($request->get('status') != Board::STATUS_NOTICE && $item->status == Board::STATUS_NOTICE) {
            $item->status = Board::STATUS_PUBLIC;
        }

        // 암호 설정
        $oldCertifyKey = $item->certifyKey;
        if ($item->certifyKey != '' && isset($inputs['certifyKey']) === true && $inputs['certifyKey'] == '') {
            $inputs['certifyKey'] = $item->certifyKey;
        } elseif ($item->certifyKey != '' && isset($inputs['certifyKey']) === true && $inputs['certifyKey'] != '') {
            $inputs['certifyKey'] = $identifyManager->hash($inputs['certifyKey']);
        }

        /** @var \Xpressengine\Editor\AbstractEditor $editor */
        $editor = XeEditor::get($this->instanceId);
        $inputs['format'] = $editor->htmlable() ? Board::FORMAT_HTML : Board::FORMAT_NONE;

        // set file, tag
        $inputs['_files'] = array_get($inputs, $editor->getFileInputName(), []);
        $inputs['_hashTags'] = array_get($inputs, $editor->getTagInputName(), []);

        $board = $this->handler->put($item, $inputs, $this->config);

        // 비회원 비밀번호를 변경 한 경우 세션 변경
        if ($oldCertifyKey != '' && $oldCertifyKey != $board->certifyKey) {
            $identifyManager->destroy($board);
            $identifyManager->create($board);
        }

        return XePresenter::makeApi([
            'item' => $board,
            'links' => [
                'article' => $this->urlHandler->get('api.article', ['id' => $board->id]),
            ],
        ]);
    }

    public function destroy(Request $request, IdentifyManager $identifyManager, $menuUrl, $id)
    {
        $user = Auth::user();

        $item = $this->handler->getModel($this->config)->find($id);

        // 비회원이 작성 한 글 인증
        if (
            $item->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            $user->getRating() != 'super'
        ) {
            // 글 보기 페이지에서 삭제하기 다시 누르면 삭제 됨
            return $this->guestId($menuUrl, $item->id, $this->urlHandler->get('show', ['id' => $item->id]));
        }

        $this->handler->trash($item, $this->config);

        $identifyManager->destroy($item);

        $queries = $request->query->all();

        return XePresenter::makeApi([
            'item' => $item,
            'links' => [
                'articles' => $this->urlHandler->get('api.articles', $queries),
            ],
        ]);
    }

    /**
     * 즐겨찾기 등록, 삭제
     *
     * @param string $menuUrl first segment
     * @param string $id      document id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function favorite($menuUrl, $id)
    {
        if (Auth::check() === false) {
            throw new AccessDeniedHttpException;
        }
        $board = $this->handler->getModel($this->config)->find($id);

        $userId = Auth::user()->getId();
        $favorite = false;
        if ($this->handler->hasFavorite($board->id, $userId) === false) {
            $this->handler->addFavorite($board->id, $userId);
            $favorite = true;
        } else {
            $this->handler->removeFavorite($board->id, $userId);
        }

        return XePresenter::makeApi(['favorite' => $favorite]);
    }
}
