<?php
/**
 * UserController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

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
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Validator;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;
use Xpressengine\User\Models\User;
use Xpressengine\User\UserInterface;

/**
 * UserController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 */
class UserController extends Controller
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

    /**
     * UserController constructor.
     *
     * @param Handler $handler
     * @param ConfigHandler $configHandler
     * @param UrlHandler $urlHandler
     * @param BoardPermissionHandler $boardPermission
     */
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

    /**
     * index page
     *
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param string                 $menuUrl
     * @return \Xpressengine\Presenter\RendererInterface
     * @throws AccessDeniedHttpException
     */
    public function index(Request $request, BoardPermissionHandler $boardPermission, $menuUrl='')
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_LIST,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        $notices = $this->notices($request, $boardPermission)->toArray();
        $articles = $this->articles($request, $boardPermission, $menuUrl)->toArray();

        return XePresenter::makeAll('index', array_merge($notices, $articles));
    }

    /**
     * get notices
     *
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission
     * @return mixed
     */
    public function notices(Request $request, BoardPermissionHandler $boardPermission)
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_LIST,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        $query = $this->handler->getModel($this->config)
            ->where('instanceId', $this->instanceId)
            ->visible()->orderBy('head', 'desc');

        if ($request->has('favorite') === true) {
            $query->leftJoin(
                'board_favorites',
                sprintf('%s.%s', $query->getQuery()->from, 'id'),
                '=',
                sprintf('%s.%s', 'board_favorites', 'targetId')
            );
            $query->where('board_favorites.userId', Auth::user()->getId());
        }

        Event::fire('xe.plugin.board.notice', [$query]);

        $items = $query->get();

        $fieldTypes = (array)$this->configHandler->getDynamicFields($this->config);

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

        return XePresenter::makeApi([
            'notices' => $items,
            'categories' => $categories,
            'fieldTypes' => $fieldTypes,
        ]);
    }

    /**
     * get articles
     *
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission
     * @param null                   $id              document id
     * @return mixed
     */
    public function articles(Request $request, BoardPermissionHandler $boardPermission, $menuUrl, $id = null)
    {
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
                    'text' => $categoryItem->word,
                ];
            }
        }

        return XePresenter::makeApi([
            'paginate' => $paginate,
            'categories' => $categories,
            'fieldTypes' => $fieldTypes,
        ]);
    }

    /**
     * show
     *
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param string                 $menuUrl         first segment
     * @param string                 $id              document id
     * @return mixed
     */
    public function show(Request $request, BoardPermissionHandler $boardPermission, $menuUrl, $id)
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_READ,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        $item = $this->get($boardPermission, $menuUrl, $id)->toArray();
        $notices = $this->notices($request, $boardPermission)->toArray();
        $articles = $this->articles($request, $boardPermission, $menuUrl, $id)->toArray();

        return XePresenter::make('show', array_merge($item, $notices, $articles));
    }

    /**
     * get article
     *
     * @param BoardPermissionHandler $boardPermission board permission
     * @param string                 $menuUrl         first segment
     * @param string                 $id              document id
     * @return mixed
     */
    public function get(BoardPermissionHandler $boardPermission, $menuUrl, $id)
    {
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

        return XePresenter::makeApi([
            'item' => $item,
            'visible' => $visible,
            'showCategoryItem' => $showCategoryItem,
        ]);
    }

    /**
     * show by slug
     *
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param string                 $menuUrl         first segment
     * @param string                 $strSlug         document slug
     * @return \Xpressengine\Presenter\RendererInterface
     * @throws Exception
     */
    public function slug(Request $request, BoardPermissionHandler $boardPermission, $menuUrl, $strSlug)
    {
        $slug = BoardSlug::where('slug', $strSlug)->where('instanceId', $this->instanceId)->first();

        if ($slug === null) {
            throw new NotFoundDocumentException;
        }

        return $this->show($request, $boardPermission, $menuUrl, $slug->targetId);
    }

    /**
     * create
     *
     * @param Request                $request         request
     * @param Validator              $validator       validator
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @return mixed
     */
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

        return XePresenter::makeAll('create', [
            'handler' => $this->handler,
            'head' => $head,
            'categories' => $categories,
            'rules' => $rules,
        ]);
    }

    /**
     * create
     *
     * @param Request                $request         request
     * @param Validator              $validator       validator
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param IdentifyManager        $identifyManager identify manager
     * @return mixed
     */
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

        $this->checkCaptcha();

        $user = Auth::user();

        $this->validate($request, $validator->getCreateRule($user, $this->config));

        $inputs = $request->all();
        $inputs['instanceId'] = $this->instanceId;
        $inputs['content'] = $request->originAll()['content'];
        $inputs['title'] = htmlspecialchars($request->originAll()['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);

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


        return XePresenter::redirect()
            ->to($this->urlHandler->getShow($board, $request->query->all()))
            ->setData(['item' => $board]);
    }

    protected function checkCaptcha()
    {
        if ($this->config->get('useCaptcha', false) === true) {
            if (app('xe.captcha')->verify() !== true) {
                throw new CaptchaNotVerifiedException;
            }
        }
    }

    public function hasSlug(Request $request)
    {
        $slug = BoardSlug::convert('', $request->get('slug'));
        $slug = BoardSlug::make($slug, $request->get('id'));

        return XePresenter::makeApi([
            'slug' => $slug,
        ]);
    }

    /**
     * edit
     *
     * @param Request                $request         request
     * @param Validator              $validator       validator
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param IdentifyManager        $identifyManager identify manager
     * @param string                 $menuUrl         first segment
     * @param string                 $id              document id
     * @return \Xpressengine\Presenter\RendererInterface
     */
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

        return XePresenter::make('edit', [
            'config' => $this->config,
            'handler' => $this->handler,
            'item' => $item,
            'parent' => $parent,
            'categories' => $categories,
            'rules' => $rules,
        ]);
    }

    /**
     * update
     *
     * @param Request                $request         request
     * @param Validator              $validator       validator
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param IdentifyManager        $identifyManager identify manager
     * @return \Xpressengine\Presenter\RendererInterface
     */
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
        // replace purifying content to origin content value
        $inputs['content'] = $request->originAll()['content'];
        $inputs['title'] = htmlspecialchars($request->originAll()['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);

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

        return XePresenter::redirect()->to(
            $this->urlHandler->getSlug(
                $item->boardSlug->slug,
                $request->query->all()
            )
        )->setData(['item' => $board]);
    }

    /**
     * 비회원 인증 페이지
     *
     * @param string $menuUrl  first segment
     * @param string $id       document id
     * @param string $referrer referrer url
     * @return mixed
     */
    public function guestId($menuUrl, $id, $referrer = null)
    {
        $item = $this->handler->getModel($this->config)->find($id);

        // 레퍼러는 현재 url
        if ($referrer == null) {
            $referrer = app('url')->current();
        }

        return XePresenter::make('guestId', [
            'item' => $item,
            'referrer' => $referrer,
        ]);
    }

    /**
     * 비회원 인증 처리
     *
     * @param Request         $request         request
     * @param IdentifyManager $identifyManager identify manager
     * @return mixed
     */
    public function guestCertify(Request $request, IdentifyManager $identifyManager, $menuUrl, $id)
    {
        $item = $this->handler->getModel($this->config)->find($id);

        if ($item->certifyKey == '') {
            throw new InvalidRequestException;
        }

        if ($request->get('email') == '') {
            throw new RequiredValueHttpException(['name' => xe_trans('xe::email')]);
        }

        if ($request->get('certifyKey') == '') {
            throw new RequiredValueHttpException(['name' => xe_trans('xe::password')]);
        }

        if ($identifyManager->verify($item, $request->get('email'), $request->get('certifyKey')) === false) {
            throw new NotMatchedCertifyKeyException;
        }

        // 인증 되었다면 DB의 인증키를 세션에 저장
        $identifyManager->create($item);

        return xeRedirect()->to($request->get('referrer', 'edit'));
    }

    /**
     * 미리보기
     *
     * @param Request                $request         request
     * @param Validator              $validator       validator
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @return mixed
     */
    public function preview(Request $request, Validator $validator, BoardPermissionHandler $boardPermission)
    {
        if (
            Gate::denies(
            BoardPermissionHandler::ACTION_CREATE,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        /** @var UserInterface $user */
        $user = Auth::user();

        // get rules
        $this->validate($request, $validator->getCreateRule($user, $this->config));

        $content = $request->originAll()['content'];
        $title = htmlspecialchars($request->originAll()['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);

        $writer = $user->getDisplayName();
        if ($request->get('writer', '') !== '') {
            $writer = $request->get('writer');
        }
        if ($this->config->get('anonymity') === true) {
            $writer = $this->config->get('anonymityName');
        }

        if ($request->get('categoryItemId', '') !== '') {

        }

        $showCategoryItem = null;
        if ($request->get('categoryItemId', '') !== '') {
            $showCategoryItem = CategoryItem::find($request->get('categoryItemId'));
        }

        /** @var \Xpressengine\Editor\AbstractEditor $editor */
        $editor = XeEditor::get($this->instanceId);
        $format = $editor->htmlable() ? Board::FORMAT_HTML : Board::FORMAT_NONE;

        return XePresenter::make('preview', [
            'config' => $this->config,
            'handler' => $this->handler,
            'currentDate' => date('Y-m-d H:i:s'),
            'title' => $title,
            'content' => $content,
            'writer' => $writer,
            'format' => $format,
            'showCategoryItem' => $showCategoryItem,
        ]);
    }

    /**
     * destroy
     *
     * @param Request         $request         request
     * @param IdentifyManager $identifyManager identify manager
     * @param string          $menuUrl         first segment
     * @param string          $id              document id
     * @return \Xpressengine\Presenter\RendererInterface
     */
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
        return xeRedirect()->to($this->urlHandler->get('index', $queries))->setData(['item' => $item]);
    }

    /**
     * trash
     *
     * @param Request $request request
     * @return mixed
     */
    public function trash(Request $request)
    {
        $user = Auth::user();
        $id = $request->get('id');

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        if ($user->getRating() != 'super' && $user->getId() != $item->id) {
            throw new AccessDeniedHttpException;
        }

        $id = $request->get('id');
        $author = Auth::user();

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        // 관리자 또는 본인 글이 아니면 접근 할 수 없음
        if ($author->getRating() !== 'super' && $author->getId() != $item->id) {
            throw new NotFoundDocumentException;
        }

        $this->handler->trash($item, $this->config);

        return redirect()->to($this->urlHandler->get('index'))->with(
            ['alert' => ['type' => 'success', 'message' => xe_trans('xe::complete')]]
        );
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

    /**
     * 투표 정보
     *
     * @param Request $request request
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function showVote(Request $request, $id)
    {
        // display 설정
        $display =['assent' => true, 'dissent' => true];
        if ($this->config->get('assent') !== true) {
            $display['assent'] = false;
        }

        if ($this->config->get('dissent') !== true) {
            $display['dissent'] = false;
        }

        $user = Auth::user();

        $board = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($board, $this->config);

        $voteCounter = $this->handler->getVoteCounter();
        $vote = $voteCounter->getByName($id, $user);

        return XePresenter::makeApi([
            'display' => $display,
            'id' => $id,
            'counts' => [
                'assent' => $board->assentCount,
                'dissent' => $board->dissentCount,
            ],
            'voteAt' => $vote['counterOption'],
        ]);
    }

    /**
     * 좋아요 추가, 삭제
     *
     * @param Request $request request
     * @param string  $menuUrl first segment
     * @param string  $option  options
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function vote(Request $request, $menuUrl, $option, $id)
    {
        $author = Auth::user();

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        try {
            $this->handler->vote($item, $author, $option);
        } catch (GuestNotSupportException $e) {
            throw new AccessDeniedHttpException;
        }

        return $this->showVote($request, $id);
    }

    /**
     * get voted user list
     *
     * @param Request $request request
     * @param string  $menuUrl first segment
     * @param string  $option  options
     */
    public function votedUsers(Request $request, $menuUrl, $option, $id)
    {
        $limit = $request->get('limit', 10);

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        $counter = $this->handler->getVoteCounter();
        $logModel = $counter->newModel();
        $logs = $logModel->where('counterName', $counter->getName())->where('targetId', $id)
            ->where('counterOption', $option)->take($limit)->get();

        return apiRender('votedUsers', [
            'urlHandler' => $this->urlHandler,
            'option' => $option,
            'item' => $item,
            'logs' => $logs,
        ]);
    }

    /**
     * get voted user modal
     *
     * @param Request $request request
     * @param string $menuUrl first segment
     * @param string $option options
     * @param string $id document id
     * @return mixed
     */
    public function votedModal(Request $request, $menuUrl, $option, $id)
    {
        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        $counter = $this->handler->getVoteCounter();
        $logModel = $counter->newModel();
        $count = $logModel->where('counterName', $counter->getName())->where('targetId', $id)
            ->where('counterOption', $option)->count();

        return apiRender('votedModal', [
            'urlHandler' => $this->urlHandler,
            'option' => $option,
            'item' => $item,
            'count' => $count,
        ]);
    }

    /**
     * get voted user list
     *
     * @param Request $request
     * @param $menuUrl
     * @param $option
     * @param $id
     * @return mixed
     */
    public function votedUserList(Request $request, $menuUrl, $option, $id)
    {
        $startId = $request->get('startId');
        $limit = $request->get('limit', 10);

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        $counter = $this->handler->getVoteCounter();
        $logModel = $counter->newModel();
        $query = $logModel->where('counterName', $counter->getName())->where('targetId', $id)
            ->where('counterOption', $option);

        if ($startId != null) {
            $query->where('id', '<', $startId);
        }

        $logs = $query->orderBy('id', 'desc')->take($limit)->get();
        $list = [];
        foreach ($logs as $log) {
            /** @var User $user */
            $user = $log->user;
            $profilePage = '#';
            if ($user->getId() != '') {
                $profilePage = route('member.profile', ['member' => $user->getId()]);
            }
            $list[] = [
                'id' => $user->getId(),
                'displayName' => $user->getDisplayName(),
                'profileImage' => $user->getProfileImage(),
                'createdAt' => (string)$log->createdAt,
                'profilePage' => $profilePage,
            ];
        }

        $nextStartId = 0;
        if (count($logs) == $limit) {
            $nextStartId = $logs->last()->id;
        }

        return XePresenter::makeApi([
            'item' => $item,
            'list' => $list,
            'nextStartId' => $nextStartId,
        ]);
    }
}
