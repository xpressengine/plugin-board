<?php
/**
 * ManagerController
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Controllers;

use Xpressengine\Keygen\Keygen;
use Xpressengine\Plugins\Board\Module\Board;
use Xpressengine\Plugins\CommentService\ManageSection as CommentSection;
use App\Sections\DynamicFieldSection;
use App\Sections\ToggleMenuSection;
use App\Sections\SkinSection;
use Input;
use View;
use Redirect;
use Exception;
use Presenter;
use App;
use XeDB;
use Xpressengine\Config\ConfigEntity;
use Cfg;
use DynamicField;
use Validator;
use App\Http\Controllers\Controller;
use Document;
use Route;
use Xpressengine\Document\DocumentEntity;
use Xpressengine\Permission\Grant;
use Xpressengine\Permission\Action;
use Xpressengine\Plugins\Board\Plugin;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\PermissionHandler;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\InstanceManager;
use Xpressengine\Plugins\Board\ExtensionHandler;
use Xpressengine\Routing\InstanceRoute;

/**
 * ManagerController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class ManagerController extends Controller
{

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var ConfigHandler
     */
    protected $configHandler;

    /**
     * @var PermissionHandler
     */
    protected $permissionHandler;

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
     * @var ExtensionHandler
     */
    protected $extensionHandler;

    /**
     * create instance
     */
    public function __construct()
    {
        /** @var \Xpressengine\Plugins\Board\Plugin $plugin */
        $this->plugin = app('xe.plugin')->getPlugin('board')->getObject();

        $this->handler = app('xe.board.handler');
        $this->configHandler = app('xe.board.config');
        $this->permissionHandler = app('xe.board.permission');
        $this->urlHandler =  app('xe.board.url');
        $this->instanceManager =  app('xe.board.instance');

        $this->presenter = app('xe.presenter');

        $this->presenter->setSettingsSkin(Board::getId());
        $this->presenter->share('handler', $this->handler);
        $this->presenter->share('permissionHandler', $this->permissionHandler);
        $this->presenter->share('configHandler', $this->configHandler);
        $this->presenter->share('urlHandler', $this->urlHandler);
    }

    /**
     * 게시판 설정 페이지
     * 게시판 최상위 설정. 인스턴스에서 별도의 설정을 하지 않았다면 이 설정을 따른다.
     * 기본 설정 중에도 몇개의 설정 만 수정 할수 있도록 한다.
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function globalEdit()
    {
        $config = $this->configHandler->getDefault();

        return $this->presenter->make('global.edit', [
            'config' => $config,
        ]);
    }

    /**
     * 게시판 설정 등록
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function globalUpdate()
    {
        $beforeConfig = $this->configHandler->getDefault();
        $inputs = Input::only(array_keys($beforeConfig->getPureAll()));

        $config = $this->configHandler->putDefault($inputs);

        return Redirect::to($this->urlHandler->managerUrl('global.edit'));
    }

    /**
     * edit
     *
     * @param string $boardId board id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function edit($boardId)
    {
        $config = $this->configHandler->get($boardId);

        $listOptions = $this->configHandler->listColumns($boardId);
        $listColumns = $config->get('listColumns');

        // 현재 선택된건 제외 시키고 보여줌
        $listOptions = array_diff($listOptions, $listColumns);

        $formColumns = $this->configHandler->formColumns($boardId);

        $boardOrders = app('xe.board.order')->gets();

		$skinSection = (new SkinSection())->setting(Board::getId(), $boardId);

        $commentSection = (new CommentSection())->setting($boardId);

        $dynamicFieldSection = (new DynamicFieldSection($config->get('documentGroup')))
            ->setting(XeDB::connection(), $config->get('revision'));

        $toggleMenuSection = (new ToggleMenuSection())->setting(Board::getId(), $boardId);

        $perms = $this->permissionHandler->getPerms($boardId);


        return $this->presenter->make('edit', [
            'config' => $config,
            'boardId' => $boardId,
            'listOptions' => $listOptions,
            'listColumns' => $listColumns,
            'formColumns' => $formColumns,
            'boardOrders' => $boardOrders,
            'skinSection' => $skinSection,
            'commentSection' => $commentSection,
            'dynamicFieldSection' => $dynamicFieldSection,
            'toggleMenuSection' => $toggleMenuSection,
            'perms' => $perms,
        ]);

    }

    /**
     * update
     *
     * @param string $boardId board id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($boardId)
    {
        $config = $this->configHandler->get($boardId);

        $permissionNames = [];
        $permissionNames['read'] = ['readMode', 'readRating', 'readUser', 'readExcept'];
        $permissionNames['list'] = ['listMode', 'listRating', 'listUser', 'listExcept'];
        $permissionNames['create'] = ['createMode', 'createRating', 'createUser', 'createExcept'];
        $permissionNames['manage'] = ['manageMode', 'manageRating', 'manageUser', 'manageExcept'];
        $inputs = Input::except(array_merge(
            ['_token'],
            $permissionNames['read'],
            $permissionNames['list'],
            $permissionNames['create'],
            $permissionNames['manage']
        ));

        //$inputs['extensions'] = isset($inputs['extensions']) ? $inputs['extensions'] : [];

        foreach ($inputs as $key => $value) {
            $config->set($key, $value);
        }

        $config = $this->instanceManager->updateConfig($config->getPureAll());

        // 확장 기능 사용
        //$this->extensionHandler->activate($inputs['extensions'], $config);

        // permission update
        $grant = new Grant();

        foreach ($this->permissionHandler->getActions() as $action) {
            $permInputs = Input::only($permissionNames[$action]);
            if ($permInputs[$action.'Mode'] == 'manual') {
                $grant = $this->permissionHandler->createGrant($grant, $action, [
                    Grant::RATING_TYPE => $permInputs[$action . 'Rating'],
                    Grant::GROUP_TYPE => isset($permInputs[$action . 'Group']) ?
                        $permInputs[$action . 'Group'] : [],
                    Grant::USER_TYPE => explode(',', $permInputs[$action . 'User']),
                    Grant::EXCEPT_TYPE => explode(',', $permInputs[$action . 'Except'])
                ]);
            }
        }

        $this->permissionHandler->set($boardId, $grant);

        return Redirect::to($this->urlHandler->managerUrl('edit', ['boardId' => $boardId]));
    }

    /**
     * document manager
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function docsIndex()
    {
        $instances = [];
        $instanceIds = [];
        $urls = [];

        /** @var \Xpressengine\Routing\InstanceRouteHandler $instanceRoute */
        $instanceRoute = app('xe.router');
        $instanceRoutes = $instanceRoute->getsByModule(Board::getId());
        foreach ($instanceRoutes as $route) {
            $instanceIds[] = $route->instanceId;
            $urls[$route->instanceId] = $route->url;
            $instances[] = [
                'id' => $route->instanceId,
                'name' => $route->instanceId,
            ];
        }

        $wheres = [
            'status' => DocumentEntity::STATUS_PUBLIC,
            'instanceIds' => $instanceIds,
        ];

        // keyword 검색 처리
        if (Input::get('searchKeyword') != '') {
            $searchTarget = Input::get('searchTarget');
            $searchKeyword = Input::get('searchKeyword');
            if ($searchTarget == 'title_content') {
                $wheres[$searchTarget] = $searchKeyword;
            } else {
                $wheres[$searchTarget] = $searchKeyword;
            }
        }

        // 상세 검색 처리
        foreach (Input::all() as $key => $value) {
            if ($value != '') {
                $wheres[$key] = $value;
            }
        }

        // 정렬 처리
        $orders = ['createdAt' => 'desc'];

        $documents = Document::paginate($wheres, $orders)->appends(Input::except('page'));

        return $this->presenter->make('docs.index', compact('documents', 'instances', 'urls'));
    }

    /**
     * document manager
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function docsApprove()
    {
        $instances = [];
        $instanceIds = [];
        $urls = [];
        $aliasRoutes = app('xe.router')->getsByModule(Board::getId());

        /** @var InstanceRoute $aliasRoute */
        foreach ($aliasRoutes as $aliasRoute) {
            $instanceIds[] = $aliasRoute->instanceId;
            $urls[$aliasRoute->instanceId] = $aliasRoute->url;
            $instances[] = [
                'id' => $aliasRoute->instanceId,
                'name' => $aliasRoute->instanceId,
            ];
        }

        $wheres = [
            'approved' => DocumentEntity::APPROVED_REJECTED,
            'instanceIds' => $instanceIds,
        ];

        // keyword 검색 처리
        if (Input::get('searchKeyword') != '') {
            $searchTarget = Input::get('searchTarget');
            $searchKeyword = Input::get('searchKeyword');
            if ($searchTarget == 'title_content') {
                $wheres[$searchTarget] = $searchKeyword;
            } else {
                $wheres[$searchTarget] = $searchKeyword;
            }
        }

        // 상세 검색 처리
        foreach (Input::all() as $key => $value) {
            if ($value != '') {
                $wheres[$key] = $value;
            }
        }

        // 정렬 처리
        $orders =['createdAt' => 'desc'];

        $documents = Document::paginate($wheres, $orders, 3)->appends(Input::except('page'));

        return $this->presenter->make('docs.approve', compact('documents', 'instances', 'urls'));
    }

    /**
     * document manager
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function docsTrash()
    {
        $instances = [];
        $instanceIds = [];
        $urls = [];

        /** @var \Xpressengine\Routing\InstanceRouteHandler $instanceRoute */
        $instanceRoute = app('xe.router');
        $instanceRoutes = $instanceRoute->getsByModule(Board::getId());
        foreach ($instanceRoutes as $route) {
            $instanceIds[] = $route->instanceId;
            $urls[$route->instanceId] = $route->url;
            $instances[] = [
                'id' => $route->instanceId,
                'name' => $route->instanceId,
            ];
        }

        $wheres = [
            'status' => DocumentEntity::STATUS_TRASH,
            'instanceIds' => $instanceIds,
        ];

        // keyword 검색 처리
        if (Input::get('searchKeyword') != '') {
            $searchTarget = Input::get('searchTarget');
            $searchKeyword = Input::get('searchKeyword');
            if ($searchTarget == 'title_content') {
                $wheres[$searchTarget] = $searchKeyword;
            } else {
                $wheres[$searchTarget] = $searchKeyword;
            }
        }

        // 상세 검색 처리
        foreach (Input::all() as $key => $value) {
            if ($value != '') {
                $wheres[$key] = $value;
            }
        }

        // 정렬 처리
        $orders =['createdAt' => 'desc'];

        $documents = Document::paginate($wheres, $orders, 3)->appends(Input::except('page'));

        return $this->presenter->make('docs.trash', compact('documents', 'instances', 'urls'));
    }

    /**
     * approve
     * 
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function approve()
    {
//        $approved = Input::get('approved');
//        $documentIds = Input::get('id');
//        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];
//
//        $docs = Document::getsByIds($documentIds);
//
//
//        foreach ($docs as $doc) {
//            $doc->approved = $approved;
//
//            Document::put($doc);
//        }

        $approved = Input::get('approved');

        $documentIds = Input::get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = $this->handler->gets(['ids'=>$documentIds], []);

        foreach ($items as $item) {
            XeDB::beginTransaction();
            $item->approved = $approved;
            $this->handler->put($item);
            XeDB::commit();
        }

        return $this->presenter->makeApi([]);


//        if (Input::get('redirect') != null) {
//            return redirect(Input::get('redirect'));
//        } else {
//            return redirect()->route('manage.xe_board.board.docs.index');
//        }
        return $this->presenter->makeApi([]);
    }

    /**
     * destroy document
     * 
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function destroy()
    {
        $documentIds = Input::get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = $this->handler->gets(['ids'=>$documentIds], []);

        foreach ($items as $item) {
            XeDB::beginTransaction();
            $this->handler->remove($item, $this->configHandler->get($item->instanceId));
            XeDB::commit();
        }

        return $this->presenter->makeApi([]);
    }

    /**
     * move to trash
     * 
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function trash()
    {
        $documentIds = Input::get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = $this->handler->gets(['ids'=>$documentIds], []);

        foreach ($items as $item) {
            XeDB::beginTransaction();
            $this->handler->trash($item, $this->configHandler->get($item->instanceId));
            XeDB::commit();
        }

        return $this->presenter->makeApi([]);
    }

    /**
     * restore from trash
     *
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function restore()
    {
        $documentIds = Input::get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = $this->handler->gets(['ids'=>$documentIds], []);

        foreach ($items as $item) {
            XeDB::beginTransaction();
            $this->handler->restore($item);
            XeDB::commit();
        }

        return $this->presenter->makeApi([]);
    }

    /**
     * change instance id
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function move()
    {
        $documentIds = Input::get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $instanceId = Input::get('instanceId');

        foreach ($documentIds as $id) {
            XeDB::beginTransaction();
            $this->handler->move($id, $this->configHandler->get($instanceId), app('xe.comment'));
            XeDB::commit();
        }

        return $this->presenter->makeApi([]);
    }

    /**
     * change instance id
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function copy()
    {
        $documentIds = Input::get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $instanceId = Input::get('instanceId');

        foreach ($documentIds as $id) {
            XeDB::beginTransaction();
            $this->handler->copy($id, $this->configHandler->get($instanceId), (new Keygen())->generate());
            XeDB::commit();
        }

        return $this->presenter->makeApi([]);
    }
}
