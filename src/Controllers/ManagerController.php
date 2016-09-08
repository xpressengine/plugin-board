<?php
/**
 * ManagerController
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

use App\Http\Sections\EditorSection;
use XeDB;
use Redirect;
use XePresenter;
use Session;
use App\Http\Controllers\Controller;
use App\Http\Sections\DynamicFieldSection;
use App\Http\Sections\ToggleMenuSection;
use App\Http\Sections\SkinSection;
use Xpressengine\Captcha\CaptchaManager;
use Xpressengine\Captcha\Exceptions\ConfigurationNotExistsException;
use Xpressengine\Category\CategoryHandler;
use Xpressengine\Http\Request;
use Xpressengine\Permission\Grant;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Exceptions\NotFoundConfigHttpException;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\InstanceManager;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Modules\Board as BoardModule;
use Xpressengine\Routing\InstanceRouteHandler;
use Xpressengine\Routing\RouteRepository;
use Xpressengine\User\Models\Guest;
use Xpressengine\User\Models\User;
use Xpressengine\Plugins\Comment\ManageSection as CommentSection;

/**
 * ManagerController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 */
class ManagerController extends Controller
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
     * @var \Xpressengine\Presenter\Presenter
     */
    protected $presenter;

    /**
     * @var UrlHandler
     */
    protected $urlHandler;

    /**
     * @var InstanceManager
     */
    protected $instanceManager;

    /**
     * create instance
     *
     * @param Handler         $handler         handler
     * @param ConfigHandler   $configHandler   board config handler
     * @param UrlHandler      $urlHandler      url handler
     * @param InstanceManager $instanceManager board instance manager
     */
    public function __construct(
        Handler $handler,
        ConfigHandler $configHandler,
        UrlHandler $urlHandler,
        InstanceManager $instanceManager
    ) {
        $this->handler = $handler;
        $this->configHandler = $configHandler;
        $this->urlHandler = $urlHandler;
        $this->instanceManager =  $instanceManager;

        $this->presenter = app('xe.presenter');

        $this->presenter->setSettingsSkinTargetId(BoardModule::getId());
        $this->presenter->share('handler', $this->handler);
        $this->presenter->share('configHandler', $this->configHandler);
        $this->presenter->share('urlHandler', $this->urlHandler);
    }

    /**
     * global config edit
     *
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param CaptchaManager         $captcha         Captcha manager
     * @return mixed|\Xpressengine\Presenter\RendererInterface
     */
    public function globalEdit(BoardPermissionHandler $boardPermission, CaptchaManager $captcha)
    {
        $config = $this->configHandler->getDefault();

        $perms = $boardPermission->getGlobalPerms();

        $toggleMenuSection = new ToggleMenuSection(BoardModule::getId());

        return $this->presenter->make('global.edit', [
            'config' => $config,
            'perms' => $perms,
            'toggleMenuSection' => $toggleMenuSection,
            'captcha' => $captcha,
        ]);
    }

    /**
     * global config update
     *
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission handler
     *
     * @return mixed
     */
    public function globalUpdate(Request $request, BoardPermissionHandler $boardPermission)
    {
        if ($request->get('useCaptcha') === 'true') {
            $driver = config('captcha.driver');
            $captcha = config("captcha.apis.$driver.siteKey");
            if (!$captcha) {
                throw new ConfigurationNotExistsException();
            }
        }

        $config = $this->configHandler->getDefault();

        $permissionNames = [];
        $permissionNames['read'] = ['readMode', 'readRating', 'readUser', 'readExcept'];
        $permissionNames['list'] = ['listMode', 'listRating', 'listUser', 'listExcept'];
        $permissionNames['create'] = ['createMode', 'createRating', 'createUser', 'createExcept'];
        $permissionNames['manage'] = ['manageMode', 'manageRating', 'manageUser', 'manageExcept'];
        $inputs = $request->except(array_merge(
            ['_token'],
            $permissionNames['read'],
            $permissionNames['list'],
            $permissionNames['create'],
            $permissionNames['manage']
        ));

        foreach ($inputs as $key => $value) {
            $config->set($key, $value);
        }

        $params = $config->getPureAll();

        XeDB::beginTransaction();

        $config = $this->configHandler->putDefault($params);

        $boardPermission->setGlobal($request);

        XeDB::commit();

        return redirect()->to($this->urlHandler->managerUrl('global.edit'));
    }

    /**
     * edit
     *
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @param CaptchaManager         $captcha         Captcha manager
     * @param string                 $boardId         board id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function edit(BoardPermissionHandler $boardPermission, CaptchaManager $captcha, $boardId)
    {
        $config = $this->configHandler->get($boardId);

        $skinSection = new SkinSection(BoardModule::getId(), $boardId);

        $dynamicFieldSection = new DynamicFieldSection(
            $config->get('documentGroup'),
            XeDB::connection(),
            $config->get('revision')
        );

        $toggleMenuSection = new ToggleMenuSection(BoardModule::getId(), $boardId);

        $editorSection = new EditorSection($boardId);

        $perms = $boardPermission->getPerms($boardId);

        return $this->presenter->make('edit', [
            'config' => $config,
            'boardId' => $boardId,
            'skinSection' => $skinSection,
            'dynamicFieldSection' => $dynamicFieldSection,
            'toggleMenuSection' => $toggleMenuSection,
            'editorSection' => $editorSection,
            'perms' => $perms,
            'captcha' => $captcha,
        ]);
    }

    /**
     * update
     *
     * @param Request $request
     * @param BoardPermissionHandler $boardPermission
     * @param string $boardId board id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, BoardPermissionHandler $boardPermission, $boardId)
    {
        if ($request->get('useCaptcha') === 'true') {
            $driver = config('captcha.driver');
            $captcha = config("captcha.apis.$driver.siteKey");
            if (!$captcha) {
                throw new ConfigurationNotExistsException();
            }
        }

        $config = $this->configHandler->get($boardId);

        $permissionNames = [];
        $permissionNames['read'] = ['readMode', 'readRating', 'readUser', 'readExcept'];
        $permissionNames['list'] = ['listMode', 'listRating', 'listUser', 'listExcept'];
        $permissionNames['create'] = ['createMode', 'createRating', 'createUser', 'createExcept'];
        $permissionNames['manage'] = ['manageMode', 'manageRating', 'manageUser', 'manageExcept'];
        $inputs = $request->except(array_merge(
            ['_token'],
            $permissionNames['read'],
            $permissionNames['list'],
            $permissionNames['create'],
            $permissionNames['manage']
        ));

        foreach ($inputs as $key => $value) {
            $config->set($key, $value);
        }

        foreach ($config->getPureAll() as $key => $value) {
            if ($config->getParent()->get($key) != null && isset($inputs[$key]) === false) {
                unset($config[$key]);
            }
        }

        XeDB::beginTransaction();

        $config = $this->instanceManager->updateConfig($config->getPureAll());

        $boardPermission->set($request, $boardId);

        XeDB::commit();

        return redirect()->to($this->urlHandler->managerUrl('edit', ['boardId' => $boardId]));
    }

    public function storeCategory(CategoryHandler $categoryHandler, Request $request)
    {
        $boardId = $request->get('boardId');
        $input = [
            'name' => $boardId . '-' . BoardModule::getId(),
        ];
        $category = $categoryHandler->create($input);

        if ($boardId == '') {
            // global config
            $config = $this->configHandler->getDefault();
            $config->set('categoryId', $category->id);
            $this->configHandler->putDefault($config->getPureAll());
        } else {
            $config = $this->configHandler->get($boardId);
            $config->set('categoryId', $category->id);
            $this->instanceManager->updateConfig($config->getPureAll());
        }


        return XePresenter::makeApi(
            $category->getAttributes()
        );
    }

    /**
     * document manager
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function docsIndex(Request $request, RouteRepository $routeRepository)
    {
        $instances = [];
        $instanceIds = [];
        $urls = [];

        $instanceRoutes = $routeRepository->fetchByModule(BoardModule::getId());
        foreach ($instanceRoutes as $route) {
            $instanceIds[] = $route->instanceId;
            $urls[$route->instanceId] = $route->url;
            $instances[] = [
                'id' => $route->instanceId,
                'name' => $route->url,
            ];
        }

        $wheres = [
            'status' => Board::STATUS_PUBLIC,
            'instanceIds' => $instanceIds,
        ];

        // keyword 검색 처리
        if ($request->get('searchKeyword') != '') {
            $searchTarget = $request->get('searchTarget');
            $searchKeyword = $request->get('searchKeyword');
            if ($searchTarget == 'title_content') {
                $wheres[$searchTarget] = $searchKeyword;
            } else {
                $wheres[$searchTarget] = $searchKeyword;
            }
        }

        // 상세 검색 처리
        foreach ($request->all() as $key => $value) {
            if ($value != '') {
                $wheres[$key] = $value;
            }
        }

        // 정렬 처리
        $orders = ['createdAt' => 'desc'];

        $query = Board::whereIn('instanceId', $instanceIds)->where('status', Board::STATUS_PUBLIC);
        $query->orderBy('createdAt', 'desc');
        $documents = $query->paginate(15)->appends($request->except('page'));

        return $this->presenter->make('docs.index', compact('documents', 'instances', 'urls'));
    }

    /**
     * document manager
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function docsApprove(Request $request, RouteRepository $routeRepository)
    {
        $instances = [];
        $instanceIds = [];
        $urls = [];

        $instanceRoutes = $routeRepository->fetchByModule(BoardModule::getId());
        foreach ($instanceRoutes as $aliasRoute) {
            $instanceIds[] = $aliasRoute->instanceId;
            $urls[$aliasRoute->instanceId] = $aliasRoute->url;
            $instances[] = [
                'id' => $aliasRoute->instanceId,
                'name' => $aliasRoute->url,
            ];
        }

        $wheres = [
            'approved' => Board::APPROVED_REJECTED,
            'instanceIds' => $instanceIds,
        ];

        // keyword 검색 처리
        if ($request->get('searchKeyword') != '') {
            $searchTarget = $request->get('searchTarget');
            $searchKeyword = $request->get('searchKeyword');
            if ($searchTarget == 'title_content') {
                $wheres[$searchTarget] = $searchKeyword;
            } else {
                $wheres[$searchTarget] = $searchKeyword;
            }
        }

        // 상세 검색 처리
        foreach ($request->all() as $key => $value) {
            if ($value != '') {
                $wheres[$key] = $value;
            }
        }

        $query = Board::whereIn('instanceId', $instanceIds)->where('approved', Board::APPROVED_REJECTED);
        $query->orderBy('createdAt', 'desc');
        $documents = $query->paginate(15)->appends($request->except('page'));

        return $this->presenter->make('docs.approve', compact('documents', 'instances', 'urls'));
    }

    /**
     * document manager
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function docsTrash(Request $request, RouteRepository $routeRepository)
    {
        $instances = [];
        $instanceIds = [];
        $urls = [];

        $instanceRoutes = $routeRepository->fetchByModule(BoardModule::getId());
        foreach ($instanceRoutes as $route) {
            $instanceIds[] = $route->instanceId;
            $urls[$route->instanceId] = $route->url;
            $instances[] = [
                'id' => $route->instanceId,
                'name' => $route->url,
            ];
        }

        $wheres = [
            'status' => Board::STATUS_TRASH,
            'instanceIds' => $instanceIds,
        ];

        // keyword 검색 처리
        if ($request->get('searchKeyword') != '') {
            $searchTarget = $request->get('searchTarget');
            $searchKeyword = $request->get('searchKeyword');
            if ($searchTarget == 'title_content') {
                $wheres[$searchTarget] = $searchKeyword;
            } else {
                $wheres[$searchTarget] = $searchKeyword;
            }
        }

        // 상세 검색 처리
        foreach ($request->all() as $key => $value) {
            if ($value != '') {
                $wheres[$key] = $value;
            }
        }

        $query = Board::whereIn('instanceId', $instanceIds)->where('status', Board::STATUS_TRASH);
        $query->orderBy('createdAt', 'desc');
        $documents = $query->paginate(15)->appends($request->except('page'));

        return $this->presenter->make('docs.trash', compact('documents', 'instances', 'urls'));
    }

    /**
     * update document approve status
     *
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function approve(Request $request)
    {
        $approved = $request->get('approved');

        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $this->handler->setModelConfig($item, $this->configHandler->get($item->instanceId));
            $this->handler->put($item, ['approve' => $approved]);
        }

        return $this->presenter->makeApi([]);
    }

    /**
     * destroy document
     *
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function destroy(Request $request)
    {
        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $this->handler->setModelConfig($item, $this->configHandler->get($item->instanceId));
            $this->handler->remove($item, $this->configHandler->get($item->instanceId));
        }

        Session::flash('alert', ['type' => 'success', 'message' => xe_trans('xe::processed')]);

        return $this->presenter->makeApi([]);
    }

    /**
     * move to trash
     *
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function trash(Request $request)
    {
        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $this->handler->setModelConfig($item, $this->configHandler->get($item->instanceId));
            $this->handler->trash($item, $this->configHandler->get($item->instanceId));
        }

        Session::flash('alert', ['type' => 'success', 'message' => xe_trans('xe::processed')]);

        return $this->presenter->makeApi([]);
    }

    /**
     * move to restore
     *
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function restore(Request $request)
    {
        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $this->handler->setModelConfig($item, $this->configHandler->get($item->instanceId));
            $this->handler->restore($item, $this->configHandler->get($item->instanceId));
        }

        return $this->presenter->makeApi([]);
    }

    /**
     * move to move
     *
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function move(Request $request)
    {
        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $instanceId = $request->get('instanceId');
        $config = $this->configHandler->get($instanceId);
        if ($config === null) {
            throw new NotFoundConfigHttpException(['instanceId' => $instanceId]);
        }

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $this->handler->setModelConfig($item, $this->configHandler->get($item->instanceId));
            $this->handler->move($item, $config);
        }

        Session::flash('alert', ['type' => 'success', 'message' => xe_trans('xe::processed')]);

        return $this->presenter->makeApi([]);
    }

    /**
     * move to copy
     *
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function copy(Request $request)
    {
        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $instanceId = $request->get('instanceId');
        $config = $this->configHandler->get($instanceId);
        if ($config === null) {
            throw new NotFoundConfigHttpException(['instanceId' => $instanceId]);
        }

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $this->handler->setModelConfig($item, $this->configHandler->get($item->instanceId));
            $user = new Guest;
            if ($item->userId != '') {
                $user = User::find($item->userId);
            }

            $this->handler->copy($item, $user, $config);
        }

        Session::flash('alert', ['type' => 'success', 'message' => xe_trans('xe::processed')]);

        return $this->presenter->makeApi([]);
    }
}
