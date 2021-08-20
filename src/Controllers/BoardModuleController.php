<?php
/**
 * BoardModuleController
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
namespace Xpressengine\Plugins\Board\Controllers;

use XeDocument;
use XePresenter;
use XeFrontend;
use XeEditor;
use XeSEO;
use XeStorage;
use XeTag;
use Auth;
use Gate;
use Event;
use App\Http\Controllers\Controller;
use Xpressengine\Category\Models\CategoryItem;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Counter\Exceptions\GuestNotSupportException;
use Xpressengine\Http\Request;
use Xpressengine\Permission\Instance;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Exceptions\AlreadyAdoptedException;
use Xpressengine\Plugins\Board\Exceptions\CanNotReplyNoticeException;
use Xpressengine\Plugins\Board\Exceptions\DisabledReplyException;
use Xpressengine\Plugins\Board\Exceptions\GuestWrittenSecretDocumentException;
use Xpressengine\Plugins\Board\Exceptions\HaveNoWritePermissionHttpException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundDocumentException;
use Xpressengine\Plugins\Board\Exceptions\NotMatchedCertifyKeyException;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\IdentifyManager;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Plugins\Board\ReplyConfigHandler;
use Xpressengine\Plugins\Board\Services\BoardService;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Validator;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;
use Xpressengine\Support\Purifier;
use Xpressengine\Support\PurifierModules\Html5;
use Xpressengine\Editor\PurifierModules\EditorContent;
use Xpressengine\User\Models\User;
use Xpressengine\User\UserInterface;

/**
 * BoardModuleController
 *
 * 메뉴에서 게시판 추가할 때 추가된 게시판 관리
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class BoardModuleController extends Controller
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
     *
     * @deprecated
     */
    public $isManager = false;

    /**
     * constructor.
     *
     * @param Handler       $handler       board handler
     * @param ConfigHandler $configHandler board config handler
     * @param UrlHandler    $urlHandler    board url handler
     */
    public function __construct(
        Handler $handler,
        ConfigHandler $configHandler,
        UrlHandler $urlHandler
    ) {
        $instanceConfig = InstanceConfig::instance();
        $this->instanceId = $instanceConfig->getInstanceId();

        $this->handler = $handler;
        $this->configHandler = $configHandler;
        $this->urlHandler = $urlHandler;
        $this->config = $configHandler->get($this->instanceId);
        if ($this->config !== null) {
            $urlHandler->setInstanceId($this->config->get('boardId'));
            $urlHandler->setConfig($this->config);
        }

        // set Skin
        XePresenter::setSkinTargetId(BoardModule::getId());
        XePresenter::share('handler', $handler);
        XePresenter::share('configHandler', $configHandler);
        XePresenter::share('urlHandler', $urlHandler);
        XePresenter::share('instanceId', $this->instanceId);
        XePresenter::share('config', $this->config);
    }

    /**
     * index page
     *
     * @param BoardService           $service         board service
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @return \Xpressengine\Presenter\Presentable
     * @throws AccessDeniedHttpException
     */
    public function index(BoardService $service, Request $request, BoardPermissionHandler $boardPermission)
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_LIST,
            new Instance($boardPermission->name($this->instanceId))
        )) {
            throw new AccessDeniedHttpException;
        }

        \XeFrontend::title($this->getSiteTitle());

        $notices = $service->getNoticeItems($request, $this->config, Auth::user()->getId());
        $paginate = $service->getItems($request, $this->config);
        $fieldTypes = $service->getFieldTypes($this->config);
        $categories = $service->getCategoryItemsTree($this->config);
        $orders = $this->handler->getOrders();
        $searchOptions = $service->getSearchOptions($request);

        $isWritable = Gate::allows(
            BoardPermissionHandler::ACTION_CREATE,
            new Instance($boardPermission->name($this->instanceId))
        );

        $dynamicFieldsById = [];
        foreach ($fieldTypes as $fieldType) {
            $dynamicFieldsById[$fieldType->get('id')] = $fieldType;
        }

        $titleHeadItems = $service->getTitleHeadItems($this->config);

        return XePresenter::makeAll('index', [
            'notices' => $notices,
            'paginate' => $paginate,
            'categories' => $categories,
            'fieldTypes' => $fieldTypes,
            'orders' => $orders,
            'dynamicFieldsById' => $dynamicFieldsById,
            'searchOptions' => $searchOptions,
            'isWritable' => $isWritable,
            'titleHeadItems' => $titleHeadItems,
        ]);
    }

    /**
     * show
     *
     * @param BoardService           $service         board service
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param string                 $menuUrl         first segment
     * @param string                 $id              document id
     * @return mixed
     */
    public function show(
        BoardService $service,
        Request $request,
        BoardPermissionHandler $boardPermission,
        $menuUrl,
        $id
    ) {
        $user = Auth::user();

        try {
            $item = $service->getItem($id, $user, $this->config, $this->isManager());
        } catch (GuestWrittenSecretDocumentException $e) {
            return xe_redirect()->to($this->urlHandler->get('guest.id', [
                'id' => $id,
                'referrer' => app('url')->current(),
            ]));
        }

        $identifyManager = app('xe.board.identify');
        if ($service->hasItemPerm($item, $user, $identifyManager, $this->isManager()) == false
            && Gate::denies(
                BoardPermissionHandler::ACTION_READ,
                new Instance($boardPermission->name($this->instanceId))
            )
        ) {
            throw new AccessDeniedHttpException;
        }

        // if use consultation option Guest cannot create article
        if ($this->config->get('useConsultation') === true
            && $service->hasItemPerm($item, $user, $identifyManager, $this->isManager()) == false
        ) {
            throw new AccessDeniedHttpException;
        }

        // 글 조회수 증가
        if ($item->display == Board::DISPLAY_VISIBLE) {
            $this->handler->incrementReadCount($item, Auth::user());
        }

        $notices = $service->getNoticeItems($request, $this->config, Auth::user()->getId());
        $paginate = $service->getItems($request, $this->config, $id);
        $fieldTypes = $service->getFieldTypes($this->config);
        $categories = $service->getCategoryItemsTree($this->config);
        $searchOptions = $service->getSearchOptions($request);
        $boardMoreItems = $service->getBoardMoreItems($this->config, $id);

        $dynamicFieldsById = [];
        foreach ($fieldTypes as $fieldType) {
            $dynamicFieldsById[$fieldType->get('id')] = $fieldType;
        }

        $thumb = $this->handler->getThumb($item->id);
        $item->setCanonical($this->urlHandler->getShow($item));
        $titleHeadItems = $service->getTitleHeadItems($this->config);

        if ($this->config->get('replyPost', false) === true) {
            $item->load('replies');
        }

        return XePresenter::make('show', [
            'item' => $item,
            'thumb' => $thumb,
            'currentItem' => $item,
            'notices' => $notices,
            'paginate' => $paginate,
            'categories' => $categories,
            'fieldTypes' => $fieldTypes,
            'dynamicFieldsById' => $dynamicFieldsById,
            'searchOptions' => $searchOptions,
            'boardMoreItems' => $boardMoreItems,
            'titleHeadItems' => $titleHeadItems,
            'replyConfig' => $this->config->get('replyPost', false) ? ReplyConfigHandler::make()->get($this->instanceId) : null,
        ]);
    }

    /**
     * show
     *
     * @param BoardService           $service         board service
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param string                 $menuUrl         first segment
     * @param string                 $id              document id
     * @return mixed
     */
    public function showModalByItemId(
        BoardService $service,
        Request $request,
        BoardPermissionHandler $boardPermission,
        $menuUrl,
        $id
    ) {
        $user = Auth::user();

        try {
            $item = $service->getItem($id, $user, $this->config, $this->isManager());
        } catch (GuestWrittenSecretDocumentException $e) {
            return xe_redirect()->to($this->urlHandler->get('guest.id', [
                'id' => $id,
                'referrer' => app('url')->current(),
            ]));
        }

        $identifyManager = app('xe.board.identify');
        if ($service->hasItemPerm($item, $user, $identifyManager, $this->isManager()) == false
            && Gate::denies(
                BoardPermissionHandler::ACTION_READ,
                new Instance($boardPermission->name($this->instanceId))
            )
        ) {
            throw new AccessDeniedHttpException;
        }

        // if use consultation option Guest cannot create article
        if ($this->config->get('useConsultation') === true
            && $service->hasItemPerm($item, $user, $identifyManager, $this->isManager()) == false
        ) {
            throw new AccessDeniedHttpException;
        }

        // 글 조회수 증가
        if ($item->display == Board::DISPLAY_VISIBLE) {
            $this->handler->incrementReadCount($item, Auth::user());
        }

        $fieldTypes = $service->getFieldTypes($this->config);
        $categories = $service->getCategoryItemsTree($this->config);
        $searchOptions = $service->getSearchOptions($request);

        $dynamicFieldsById = [];
        foreach ($fieldTypes as $fieldType) {
            $dynamicFieldsById[$fieldType->get('id')] = $fieldType;
        }

        $thumb = $this->handler->getThumb($item->id);

        $item->setCanonical($this->urlHandler->getShow($item));

        $titleHeadItems = $service->getTitleHeadItems($this->config);

        return api_render('showModal', [
            'item' => $item,
            'thumb' => $thumb,
            'currentItem' => $item,
            'categories' => $categories,
            'fieldTypes' => $fieldTypes,
            'dynamicFieldsById' => $dynamicFieldsById,
            'searchOptions' => $searchOptions,
            'titleHeadItems' => $titleHeadItems,
        ]);
    }

    /**
     * print
     *
     * @param BoardService           $service         board service
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission
     * @param string                 $menuUrl         menu url
     * @param string                 $id              board id
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function print(
        BoardService $service,
        Request $request,
        BoardPermissionHandler $boardPermission,
        $menuUrl,
        $id
    ) {
        $user = Auth::user();
        $item = $service->getItem($id, $user, $this->config, $this->isManager());

        $identifyManager = app('xe.board.identify');
        if ($service->hasItemPerm($item, $user, $identifyManager, $this->isManager()) == false
            && Gate::denies(
                BoardPermissionHandler::ACTION_READ,
                new Instance($boardPermission->name($this->instanceId))
            )
        ) {
            throw new AccessDeniedHttpException;
        }

        // if use consultation option Guest cannot create article
        if ($this->config->get('useConsultation') === true
            && $service->hasItemPerm($item, $user, $identifyManager, $this->isManager()) == false
        ) {
            throw new AccessDeniedHttpException;
        }

        $fieldTypes = $service->getFieldTypes($this->config);
        $categories = $service->getCategoryItemsTree($this->config);

        $dynamicFieldsById = [];
        foreach ($fieldTypes as $fieldType) {
            $dynamicFieldsById[$fieldType->get('id')] = $fieldType;
        }

        XePresenter::htmlRenderPopup();

        $thumb = $this->handler->getThumb($item->id);

        return XePresenter::make('print', [
            'item' => $item,
            'thumb' => $thumb,
            'categories' => $categories,
            'fieldTypes' => $fieldTypes,
            'dynamicFieldsById' => $dynamicFieldsById,
        ]);
    }

    /**
     * show by slug
     *
     * @param BoardService           $service         board service
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param string                 $menuUrl         first segment
     * @param string                 $strSlug         document slug
     * @return \Xpressengine\Presenter\Presentable
     */
    public function slug(
        BoardService $service,
        Request $request,
        BoardPermissionHandler $boardPermission,
        $menuUrl,
        $strSlug
    ) {
        $slug = BoardSlug::where('slug', $strSlug)->where('instance_id', $this->instanceId)->first();

        if ($slug === null) {
            throw new NotFoundDocumentException;
        }

        if ($this->config->get('urlType') !== 'slug') {
            return redirect(instance_route('show', ['id' => $slug->target_id]));
        }

        return $this->show($service, $request, $boardPermission, $menuUrl, $slug->target_id);
    }

    /**
     * show by serial number
     *
     * @param BoardService           $service         board service
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param string                 $menuUrl         first segment
     * @param string                 $serialNumber    document serial number
     * @return \Xpressengine\Presenter\Presentable
     */
    public function num(
        BoardService $service,
        Request $request,
        BoardPermissionHandler $boardPermission,
        $menuUrl,
        $serialNumber
    ) {
        $slug = BoardSlug::where('id', $serialNumber)->where('instance_id', $this->instanceId)->first();

        if ($slug === null) {
            throw new NotFoundDocumentException;
        }

        if ($this->config->get('urlType') !== 'serialNumber') {
            return redirect(instance_route('show', ['id' => $slug->target_id]));
        }

        return $this->show($service, $request, $boardPermission, $menuUrl, $slug->target_id);
    }

    /**
     * show by itemId
     *
     * @param BoardService           $service         board service
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param string                 $menuUrl         first segment
     * @param string                 $itemId          document id
     * @return \Xpressengine\Presenter\Presentable
     */
    public function showByItemId(
        BoardService $service,
        Request $request,
        BoardPermissionHandler $boardPermission,
        $menuUrl,
        $itemId
    ) {
        if ($this->config->get('urlType') !== 'documentId') {
            $slug = BoardSlug::where('target_id', $itemId)->where('instance_id', $this->instanceId)->first();

            if ($slug === null) {
                throw new NotFoundDocumentException;
            }

            if ($this->config->get('urlType') == 'slug') {
                return redirect(instance_route('slug', ['slug' => $slug->slug]));
            } else {
                return redirect(instance_route('num', ['serialNumber' => $slug->id]));
            }


        }

        return $this->show($service, $request, $boardPermission, $menuUrl, $itemId);
    }

    /**
     * create
     *
     * @param BoardService           $service         board service
     * @param Request                $request         request
     * @param Validator              $validator       validator
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @return mixed
     */
    public function create(
        BoardService $service,
        Request $request,
        Validator $validator,
        BoardPermissionHandler $boardPermission
    ) {
        if (! $boardPermission->checkCreateAction($this->instanceId)) {
            throw new AccessDeniedHttpException;
        }

        // if use consultation option Guest cannot create article
        if ($this->config->get('useConsultation') === true && Auth::check() === false) {
            throw new AccessDeniedHttpException;
        }

        // check validated parent's board
        $parentBoard = null;
        if ($parentId = $request->get('parent_id')) {
            if ($this->config->get('replyPost', false) === false) {
                throw new DisabledReplyException;
            }

            $parentBoard = Board::division($this->config->get('boardId'))
                ->where('instance_id', $this->instanceId)
                ->findOrFail($parentId);

            if ($parentBoard->isNotice()) {
                throw new CanNotReplyNoticeException;
            }
        }

        $categories = $service->getCategoryItemsTree($this->config);
        $rules = $validator->getCreateRule(Auth::user(), $this->config);
        $fieldTypes = $service->getFieldTypes($this->config);

        $dynamicFieldsById = [];
        foreach ($fieldTypes as $fieldType) {
            $dynamicFieldsById[$fieldType->get('id')] = $fieldType;
        }

        $titleHeadItems = $service->getTitleHeadItems($this->config);

        XeSEO::notExec();

        return XePresenter::makeAll('create', [
            'categories' => $categories,
            'rules' => $rules,
            'head' => '',
            'fieldTypes' => $fieldTypes,
            'dynamicFieldsById' => $dynamicFieldsById,
            'titleHeadItems' => $titleHeadItems,
            'parentBoard' => $parentBoard
        ]);
    }

    /**
     * create
     *
     * @param BoardService           $service         board service
     * @param Request                $request         request
     * @param Validator              $validator       validator
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param IdentifyManager        $identifyManager identify manager
     * @return mixed
     */
    public function store(
        BoardService $service,
        Request $request,
        Validator $validator,
        BoardPermissionHandler $boardPermission,
        IdentifyManager $identifyManager
    ) {
        if (! $boardPermission->checkCreateAction($this->instanceId)) {
            throw new AccessDeniedHttpException;
        }

        // if use consultation option Guest cannot create article
        if ($this->config->get('useConsultation') === true && Auth::check() === false) {
            throw new AccessDeniedHttpException;
        }

        $purifier = new Purifier();
        $purifier->allowModule(EditorContent::class);
        $purifier->allowModule(HTML5::class);

        $inputs = $request->all();
        $originInputs = $request->originAll();
        $inputs['title'] = htmlspecialchars($originInputs['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);

        if ($this->isManager()) {
            $inputs['content'] = $originInputs['content'];
        } else {
            $inputs['content'] = $purifier->purify($originInputs['content']);
        }

        $request->replace($inputs);

        // 유효성 체크
        $this->validate($request, $validator->getCreateRule(Auth::user(), $this->config));

        // 공지 등록 권한 확인
        if ($request->get('status') == Board::STATUS_NOTICE && $this->isManager() === false) {
            throw new HaveNoWritePermissionHttpException(['name' => xe_trans('xe::notice')]);
        }

        // 비밀글 등록 설정 확인
        if ($request->get('display') == Board::DISPLAY_SECRET && $this->config->get('secretPost') !== true) {
            throw new HaveNoWritePermissionHttpException(['name' => xe_trans('board::secretPost')]);
        }

        $item = $service->store($request, Auth::user(), $this->config, $identifyManager);

        if($request->has('redirect_url')) {
            if($request->has('redirect_message')) {
                return XePresenter::redirect()
                    ->to($request->get('redirect_url'))->with('alert', ['type' => 'success', 'message' => $request->get('redirect_message')]);
            }
            return XePresenter::redirect()
                ->to($request->get('redirect_url'));
        }

        return XePresenter::redirect()
            ->to($this->urlHandler->getShow($item, $request->query->all()))
            ->setData(['item' => $item]);
    }

    /**
     * 문자열을 넘겨 slug 반환
     *
     * @param Request $request request
     * @return mixed
     */
    public function hasSlug(Request $request)
    {
        $slugText = BoardSlug::convert('', $request->get('slug'));
        $slug = BoardSlug::make($slugText, $request->get('id'));

        return XePresenter::makeApi([
            'slug' => $slug,
        ]);
    }

    /**
     * edit
     *
     * @param BoardService    $service         board service
     * @param Request         $request         request
     * @param Validator       $validator       validator
     * @param IdentifyManager $identifyManager identify manager
     * @param string          $menuUrl         first segment
     * @param string          $id              document id
     * @return \Xpressengine\Presenter\Presentable
     */
    public function edit(
        BoardService $service,
        Request $request,
        Validator $validator,
        IdentifyManager $identifyManager,
        $menuUrl,
        $id
    ) {
        $item = Board::division($this->instanceId)->find($id);

        if ($item === null) {
            throw new NotFoundDocumentException;
        }

        // 비회원이 작성 한 글일 때 인증페이지로 이동
        if ($this->isManager() !== true &&
            $item->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            Auth::user()->getRating() != 'super') {
            return xe_redirect()->to($this->urlHandler->get('guest.id', [
                'id' => $item->id,
                'referrer' => app('url')->current(),
            ]));
        }

        // 접근 권한 확인
        if ($service->hasItemPerm($item, Auth::user(), $identifyManager, $this->isManager()) == false) {
            throw new AccessDeniedHttpException;
        }

        $categories = $service->getCategoryItemsTree($this->config);

        $rules = $validator->getEditRule(Auth::user(), $this->config);

        $fieldTypes = $service->getFieldTypes($this->config);

        $dynamicFieldsById = [];
        foreach ($fieldTypes as $fieldType) {
            $dynamicFieldsById[$fieldType->get('id')] = $fieldType;
        }

        $thumb = $this->handler->getThumb($item->id);

        $titleHeadItems = $service->getTitleHeadItems($this->config);

        XeSEO::notExec();

        return XePresenter::make('edit', [
            'item' => $item,
            'thumb' => $thumb,
            'categories' => $categories,
            'rules' => $rules,
            'parent' => null,
            'fieldTypes' => $fieldTypes,
            'dynamicFieldsById' => $dynamicFieldsById,
            'titleHeadItems' => $titleHeadItems,
        ]);
    }

    /**
     * update
     *
     * @param BoardService    $service         board service
     * @param Request         $request         request
     * @param Validator       $validator       validator
     * @param IdentifyManager $identifyManager identify manager
     * @param string          $menuUrl         first segment
     * @return \Xpressengine\Presenter\Presentable
     */
    public function update(
        BoardService $service,
        Request $request,
        Validator $validator,
        IdentifyManager $identifyManager,
        $menuUrl
    ) {
        $item = Board::division($this->instanceId)->find($request->get('id'));

        // 비회원이 작성 한 글 인증
        if ($this->isManager() !== true &&
            $item->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            Auth::user()->getRating() != 'super') {
            return xe_redirect()->to($this->urlHandler->get('guest.id', [
                'id' => $item->id,
                'referrer' => $this->urlHandler->get('edit', ['id' => $item->id]),
            ]));
        }

        $purifier = new Purifier();
        $purifier->allowModule(EditorContent::class);
        $purifier->allowModule(HTML5::class);

        $inputs = $request->all();
        $originInputs = $request->originAll();
        $inputs['title'] = htmlspecialchars($originInputs['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);

        if ($this->isManager()) {
            $inputs['content'] = $originInputs['content'];
        } else {
            $inputs['content'] = $purifier->purify($originInputs['content']);
        }

        $request->replace($inputs);

        $this->validate($request, $validator->getEditRule(Auth::user(), $this->config));

        if ($service->hasItemPerm($item, Auth::user(), $identifyManager, $this->isManager()) == false) {
            throw new AccessDeniedHttpException;
        }

        // 공지 등록 확인
        if ($request->get('status') == Board::STATUS_NOTICE) {
            if ($this->isManager() === false) {
                throw new HaveNoWritePermissionHttpException(['name' => xe_trans('xe::notice')]);
            }
        }

        // 비밀글 등록 설정 확인
        if ($request->get('display') == Board::DISPLAY_SECRET && $this->config->get('secretPost') !== true) {
            throw new HaveNoWritePermissionHttpException(['name' => xe_trans('board::secretPost')]);
        }

        $item = $service->update($item, $request, Auth::user(), $this->config, $identifyManager);

        if($request->has('redirect_url')) {
            if($request->has('redirect_message')) {
                return XePresenter::redirect()
                    ->to($request->get('redirect_url'))->with('alert', ['type' => 'success', 'message' => $request->get('redirect_message')]);
            }
            return XePresenter::redirect()
                ->to($request->get('redirect_url'));
        }

        return XePresenter::redirect()->to(
            $this->urlHandler->getShow(
                $item,
                $request->query->all()
            )
        )->setData(['item' => $item]);
    }

    /**
     * 비회원 인증 페이지
     *
     * @param Request   $request   request
     * @param Validator $validator validator
     * @param string    $menuUrl   first segment
     * @param string    $id        document id
     * @param string    $referrer  referrer url
     * @return mixed
     */
    public function guestId(Request $request, Validator $validator, $menuUrl, $id, $referrer = null)
    {
        $item = Board::division($this->instanceId)->find($id);

        // 레퍼러는 현재 url
        if ($referrer == null) {
            $referrer = app('url')->current();
        }

        if ($request->has('referrer')) {
            $referrer = $request->get('referrer');
        }

        return XePresenter::make('guestId', [
            'item' => $item,
            'referrer' => $referrer,
            'rules' => $validator->guestCertifyRule(),
        ]);
    }

    /**
     * 비회원 인증 처리
     *
     * @param Request         $request         request
     * @param IdentifyManager $identifyManager identify manager
     * @param Validator       $validator       validator
     * @param string          $menuUrl         first segment
     * @param string          $id              document id
     * @return mixed
     */
    public function guestCertify(
        Request $request,
        IdentifyManager $identifyManager,
        Validator $validator,
        $menuUrl,
        $id
    ) {
        $item = Board::division($this->instanceId)->find($id);

        $this->validate($request, $validator->guestCertifyRule());

        if ($identifyManager->verify($item, $request->get('email'), $request->get('certify_key')) === false) {
            throw new NotMatchedCertifyKeyException;
        }

        // 인증 되었다면 DB의 인증키를 세션에 저장
        $identifyManager->create($item);

        return xe_redirect()->to($request->get('referrer', 'edit'));
    }

    /**
     * 미리보기
     *
     * @param Request                $request         request
     * @param BoardService           $service         board service
     * @param Validator              $validator       validator
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @return mixed
     */
    public function preview(
        Request $request,
        BoardService $service,
        Validator $validator,
        BoardPermissionHandler $boardPermission
    ) {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_CREATE,
            new Instance($boardPermission->name($this->instanceId))
        )) {
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

        $fieldTypes = $service->getFieldTypes($this->config);
        $dynamicFieldsById = [];
        foreach ($fieldTypes as $fieldType) {
            $dynamicFieldsById[$fieldType->get('id')] = $fieldType;
        }

        $showCategoryItem = null;
        if ($request->get('category_item_id', '') !== '') {
            $showCategoryItem = CategoryItem::find($request->get('category_item_id'));
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
            'dynamicFieldsById' => $dynamicFieldsById,
            'fieldTypes' => $fieldTypes,
        ]);
    }

    /**
     * destroy
     *
     * @param BoardService    $service         board service
     * @param Request         $request         request
     * @param Validator       $validator       validator
     * @param IdentifyManager $identifyManager identify manager
     * @param string          $menuUrl         first segment
     * @param string          $id              document id
     * @return \Xpressengine\Presenter\Presentable
     */
    public function destroy(
        BoardService $service,
        Request $request,
        Validator $validator,
        IdentifyManager $identifyManager,
        $menuUrl,
        $id
    ) {
        /** @var Board $item */
        $item = Board::division($this->instanceId)->find($id);

        if ($item === null) {
            throw new NotFoundDocumentException;
        }

        // 비회원이 작성 한 글 인증
        if ($item->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            Auth::user()->getRating() != 'super') {
            return xe_redirect()->to($this->urlHandler->get('guest.id', [
                'id' => $item->id,
                'referrer' => $this->urlHandler->get('show', ['id' => $item->id]),
            ]));
        }

        if ($service->hasItemPerm($item, Auth::user(), $identifyManager, $this->isManager()) == false) {
            throw new AccessDeniedHttpException;
        }

        $service->destroy($item, $this->config, $identifyManager);

        if($request->has('redirect_url')) {
            if($request->has('redirect_message')) {
                return XePresenter::redirect()
                    ->to($request->get('redirect_url'))->with('alert', ['type' => 'success', 'message' => $request->get('redirect_message')]);
            }

            return XePresenter::redirect()
                ->to($request->get('redirect_url'));
        }

        return xe_redirect()->to(
            $this->urlHandler->get('index', $request->query->all())
        )->setData(['item' => $item]);
    }

    /**
     * trash
     *
     * @param BoardService $service board service
     * @param Request      $request request
     * @return mixed
     * @throws \Exception
     */
    public function trash(BoardService $service, Request $request)
    {
        $user = Auth::user();
        $id = $request->get('id');

        $item = Board::division($this->instanceId)->find($id);

        if ($user->getRating() != 'super' && $user->getId() != $item->userId) {
            throw new AccessDeniedHttpException;
        }

        // use page resolver
        $items = $service->getItems($request, $this->config, $id);

        $this->handler->trash($item, $this->config);

        return xe_redirect()->to(
            $this->urlHandler->get('index', $request->query->all())
        )->setData([
            'item' => $item,
        ]);
    }

    /**
     * 즐겨찾기 등록, 삭제
     *
     * @param string $menuUrl first segment
     * @param string $id      document id
     * @return \Xpressengine\Presenter\Presentable
     */
    public function favorite($menuUrl, $id)
    {
        if (Auth::check() === false) {
            throw new AccessDeniedHttpException;
        }
        $item = Board::division($this->instanceId)->find($id);

        $userId = Auth::user()->getId();
        $favorite = false;
        if ($this->handler->hasFavorite($item->id, $userId) === false) {
            $this->handler->addFavorite($item->id, $userId);
            $favorite = true;
        } else {
            $this->handler->removeFavorite($item->id, $userId);
        }

        return XePresenter::makeApi(['favorite' => $favorite]);
    }

    /**
     * 투표 정보
     *
     * @param Request $request request
     * @param string  $id      document id
     * @return \Xpressengine\Presenter\Presentable
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

        $item = Board::division($this->instanceId)->find($id);

        $voteCounter = $this->handler->getVoteCounter();
        $vote = $voteCounter->getByName($id, $user);

        return XePresenter::makeApi([
            'display' => $display,
            'id' => $id,
            'counts' => [
                'assent' => $item->assent_count,
                'dissent' => $item->dissent_count,
            ],
            'voteAt' => $vote ? $vote->counter_option : null,
        ]);
    }

    /**
     * 좋아요 추가, 삭제
     *
     * @param Request $request request
     * @param string  $menuUrl first segment
     * @param string  $option  options
     * @param string  $id      document id
     * @return \Xpressengine\Presenter\Presentable
     */
    public function vote(Request $request, $menuUrl, $option, $id)
    {
        $author = Auth::user();

        $item = Board::division($this->instanceId)->find($id);

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
     * @param string  $id      document id
     * @return mixed
     */
    public function votedUsers(Request $request, $menuUrl, $option, $id)
    {
        $limit = $request->get('limit', 10);

        $item = Board::division($this->instanceId)->find($id);

        $counter = $this->handler->getVoteCounter();
        $logModel = $counter->newModel();
        $logs = $logModel->where('counter_name', $counter->getName())->where('target_id', $id)
            ->where('counter_option', $option)->take($limit)->get();

        return api_render('votedUsers', [
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
     * @param string  $menuUrl first segment
     * @param string  $option  options
     * @param string  $id      document id
     * @return mixed
     */
    public function votedModal(Request $request, $menuUrl, $option, $id)
    {
        $item = Board::division($this->instanceId)->find($id);

        $counter = $this->handler->getVoteCounter();
        $logModel = $counter->newModel();
        $count = $logModel->where('counter_name', $counter->getName())->where('target_id', $id)
            ->where('counter_option', $option)->count();

        return api_render('votedModal', [
            'urlHandler' => $this->urlHandler,
            'option' => $option,
            'item' => $item,
            'count' => $count,
        ]);
    }

    /**
     * get voted user list
     *
     * @param Request $request request
     * @param string  $menuUrl first segment
     * @param string  $option  options
     * @param string  $id      document id
     * @return mixed
     */
    public function votedUserList(Request $request, $menuUrl, $option, $id)
    {
        $startId = $request->get('startId');
        $limit = $request->get('limit', 10);

        $item = Board::division($this->instanceId)->find($id);

        $counter = $this->handler->getVoteCounter();
        $logModel = $counter->newModel();
        $query = $logModel->where('counter_name', $counter->getName())->where('target_id', $id)
            ->where('counter_option', $option);

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
                $profilePage = route('user.profile', ['user' => $user->getId()]);
            }
            $list[] = [
                'id' => $user->getId(),
                'displayName' => $user->getDisplayName(),
                'profileImage' => $user->getProfileImage(),
                'createdAt' => (string)$log->created_at,
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

    /**
     * get favorite user list
     *
     * @param Request $request request
     * @param string  $menuUrl first segment
     * @param string  $id      document id
     * @return mixed
     */
    public function favoriteUserList(Request $request, $menuUrl, $id)
    {
        $startId = $request->get('startId');
        $limit = $request->get('limit', 10);

        $item = Board::division($this->instanceId)->findOrFail($id);
        $query = $item->favoriteUsers()->when($startId, function($query, $startId) {
            $query->where('id', '<', $startId);
        });

        $favoriteUsers = $query->orderBy('id', 'desc')->take($limit)->get();
        $nextStartId = count($favoriteUsers) == $limit ? $favoriteUsers->last()->id : 0;

        $favoriteUsers->each(function($favoriteUser) {
            $favoriteUser->addVisible(['profileImage']);
        });

        return XePresenter::makeApi([
            'item' => $item,
            'list' => $favoriteUsers,
            'nextStartId' => $nextStartId,
        ]);
    }

    /**
     * adopt (채택하다)
     *
     * @param Request $request
     * @param string $menuUrl
     * @param string $id
     * @return mixed
     */
    public function adopt(Request $request, string $menuUrl, string $id)
    {
        $item = Board::division($this->instanceId)->findOrFail($id);
        $replyConfig = ReplyConfigHandler::make()->getByBoardConfig($this->instanceId);

        if (is_null($replyConfig)) {
            throw new DisabledReplyException; // 답글을 사용하지 않는 상태인 경우.
        }

        /** @var Board $parentBoard */
        $parentBoard = $item->hasParentDoc() ? Board::with('replies', 'data')->findOrFail($item->parent_id) : null;

        if ($parentBoard->hasAdopted() === true) {
            throw new AlreadyAdoptedException; // 이미 채택된 답글이 있습니다.
        }

        $parentBoard->getAttribute('data')->adopt_id = $item->id;
        $parentBoard->getAttribute('data')->adopt_at = now();
        $parentBoard->getAttribute('data')->save();

        if($request->has('redirect_url')) {
            if($request->has('redirect_message')) {
                return XePresenter::redirect()
                    ->to($request->get('redirect_url'))->with('alert', ['type' => 'success', 'message' => $request->get('redirect_message')]);
            }
            return XePresenter::redirect()
                ->to($request->get('redirect_url'));
        }

        return XePresenter::redirect()
            ->to($this->urlHandler->getShow($parentBoard, $request->query->all()))
            ->setData(['item' => $parentBoard]);
    }

    /**
     * Un Adopt (채택을 취소합니다.)
     *
     * @param Request $request
     * @param string $menuUrl
     * @param string $id
     * @return mixed
     */
    public function unAdopt(Request $request, string $menuUrl, string $id)
    {
        /** @var Board $item */
        $item = Board::division($this->instanceId)->findOrFail($id);
        $replyConfig = ReplyConfigHandler::make()->getByBoardConfig($this->instanceId);

        if (is_null($replyConfig)) {
            throw new DisabledReplyException; // 답글을 사용하지 않는 상태인 경우.
        }

        /** @var Board $parentBoard */
        $parentBoard = $item->hasParentDoc() ? Board::with('replies', 'data')->findOrFail($item->parent_id) : null;

        if ($item->isAdopted($parentBoard) === false) {
            throw new \LogicException('This is not an adopted reply.');
        }

        $parentBoard->getAttribute('data')->adopt_id = null;
        $parentBoard->getAttribute('data')->adopt_at = null;
        $parentBoard->getAttribute('data')->save();

        if($request->has('redirect_url')) {
            if($request->has('redirect_message')) {
                return XePresenter::redirect()
                    ->to($request->get('redirect_url'))->with('alert', ['type' => 'success', 'message' => $request->get('redirect_message')]);
            }
            return XePresenter::redirect()
                ->to($request->get('redirect_url'));
        }

        return XePresenter::redirect()
            ->to($this->urlHandler->getShow($parentBoard, $request->query->all()))
            ->setData(['item' => $parentBoard]);
    }

    /**
     * is manager
     *
     * @return bool
     */
    protected function isManager()
    {
        $boardPermission = app('xe.board.permission');
        return Gate::allows(
            BoardPermissionHandler::ACTION_MANAGE,
            new Instance($boardPermission->name($this->instanceId))
        ) ? true : false;
    }

    /**
     * get site title
     *
     * @return string
     */
    private function getSiteTitle()
    {
        $siteTitle = \XeFrontend::output('title');

        $instanceConfig = InstanceConfig::instance();
        $menuItem = $instanceConfig->getMenuItem();

        $title = xe_trans($menuItem['title']) . ' - ' . xe_trans($siteTitle);
        $title = strip_tags(html_entity_decode($title));

        return $title;
    }
}
