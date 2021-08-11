<?php
/**
 * BoardSettingsController
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
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Exceptions\NotFoundConfigHttpException;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\InstanceManager;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Plugins\Comment\Exceptions\InvalidArgumentException;
use Xpressengine\Routing\InstanceRouteHandler;
use Xpressengine\Routing\RouteRepository;
use Xpressengine\User\Models\Guest;
use Xpressengine\User\Models\User;
use Xpressengine\Plugins\Comment\ManageSection as CommentSection;

/**
 * BoardSettingsController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class BoardSettingsController extends Controller
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
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function editGlobalConfig(BoardPermissionHandler $boardPermission, CaptchaManager $captcha)
    {
        $config = $this->configHandler->getDefault();

        $perms = $boardPermission->getGlobalPerms();

        $toggleMenuSection = new ToggleMenuSection(BoardModule::getId());

        return $this->presenter->make('global.config', [
            'config' => $config,
            'perms' => $perms,
            'toggleMenuSection' => $toggleMenuSection,
            'captcha' => $captcha,
        ]);
    }

    /**
     * global config update
     *
     * @param Request $request request
     * @return mixed
     */
    public function updateGlobalConfig(Request $request)
    {
        if ($request->get('useCaptcha') === 'true' && !app('xe.captcha')->available()) {
            throw new ConfigurationNotExistsException();
        }

        $config = $this->configHandler->getDefault();
        $inputs = $request->except('_token');

        foreach ($inputs as $key => $value) {
            $config->set($key, $value);
        }

        $params = $config->getPureAll();
        $this->configHandler->putDefault($params);

        return redirect()->to($this->urlHandler->managerUrl('global.config'));
    }

    /**
     * global permission edit
     *
     * @param BoardPermissionHandler $boardPermission board permission handler
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function editGlobalPermission(BoardPermissionHandler $boardPermission)
    {
        $perms = $boardPermission->getGlobalPerms();

        return $this->presenter->make('global.permission', [
            'perms' => $perms,
        ]);
    }

    /**
     * global permission update
     *
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission
     * @return mixed
     */
    public function updateGlobalPermission(Request $request, BoardPermissionHandler $boardPermission)
    {
        $boardPermission->setGlobal($request);

        return redirect()->to($this->urlHandler->managerUrl('global.permission'));
    }

    /**
     * global board toggle menu
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function editGlobalToggleMenu()
    {
        $toggleMenuSection = new ToggleMenuSection(BoardModule::getId());

        return $this->presenter->make('global.toggleMenu', [
            'toggleMenuSection' => $toggleMenuSection,
        ]);
    }

    /**
     * edit
     *
     * @param CaptchaManager $captcha Captcha manager
     * @param string         $boardId board instance id
     * @return \Xpressengine\Presenter\Presentable
     */
    public function editConfig(CaptchaManager $captcha, $boardId)
    {
        $config = $this->configHandler->get($boardId);

        $sortListColumns = $this->configHandler->getSortListColumns($config);
        $sortFormColumns = $this->configHandler->getSortFormColumns($config);

        $dynamicFields = [];
        $fieldTypes = $this->configHandler->getDynamicFields($config);
        foreach ($fieldTypes as $fieldType) {
            $dynamicFields[$fieldType->get('id')] = $fieldType;
        }

        return $this->presenter->make('module.config', [
            'config' => $config,
            'boardId' => $boardId,
            'captcha' => $captcha,
            'sortListColumns' => $sortListColumns,
            'sortFormColumns' => $sortFormColumns,
            'dynamicFields' => $dynamicFields
        ]);
    }

    /**
     * update
     *
     * @param Request $request request
     * @param string  $boardId board instance id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateConfig(Request $request, $boardId)
    {
        if ($request->get('useCaptcha') === 'true' && !app('xe.captcha')->available()) {
            throw new ConfigurationNotExistsException();
        }

        $config = $this->configHandler->get($boardId);
        $inputs = $request->except('_token');

        //관리자 메일 상위 설정에 따르지 않고 해당 게시판은 null로 설정 가능하도록 추가
        if (isset($inputs['managerEmailInherit'])) {
            unset($inputs['managerEmailInherit']);
            unset($inputs['managerEmail']);
        } else {
            if (isset($inputs['managerEmail']) === false) {
                $inputs['managerEmail'] = '';
            }
        }

        if (isset($inputs['listColumns']) === false) {
            $inputs['listColumns'] = [];
        }

        foreach ($inputs as $key => $value) {
            $config->set($key, $value);
        }

        // 상위 설정 따름 처리로 disable 된 항목 제거
        $unsetKeys = [];

        foreach ($config->getPureAll() as $key => $value) {
            // 기본 설정이 아닌 항목 예외 처리
            if (in_array($key, [
                    'listColumns','formColumns','sortListColumns','sortFormColumns'
                ]) == true) {
                continue;
            }
            if ($config->getParent()->get($key) !== null && isset($inputs[$key]) === false) {
                unset($config[$key]);
                $unsetKeys[] = $key;
            }
        }

        $this->instanceManager->updateConfig($config->getPureAll(), $unsetKeys);

        return redirect()->to($this->urlHandler->managerUrl('config', ['boardId' => $boardId]));
    }

    /**
     * store category
     *
     * @param CategoryHandler $categoryHandler category handler
     * @param Request         $request         request
     * @return mixed
     */
    public function storeCategory(CategoryHandler $categoryHandler, Request $request)
    {
        $boardId = $request->get('boardId');
        $input = [
            'name' => MenuItem::where('id', $boardId)->get()->first()->title
        ];

        $category = $categoryHandler->createCate($input);

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
     * edit permission
     *
     * @param BoardPermissionHandler $boardPermission board permission
     * @param string                 $boardId         board id
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function editPermission(BoardPermissionHandler $boardPermission, $boardId)
    {
        $config = $this->configHandler->get($boardId);

        $perms = $boardPermission->getPerms($boardId);

        return $this->presenter->make('module.permission', [
            'config' => $config,
            'boardId' => $boardId,
            'perms' => $perms,
        ]);
    }

    /**
     * update permission
     * @param Request                $request         request
     * @param BoardPermissionHandler $boardPermission board permission
     * @param string                 $boardId         board id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePermission(Request $request, BoardPermissionHandler $boardPermission, $boardId)
    {
        $boardPermission->set($request, $boardId);

        return redirect()->to($this->urlHandler->managerUrl('permission', ['boardId' => $boardId]));
    }

    /**
     * edit skin
     *
     * @param string $boardId board id
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function editSkin($boardId)
    {
        $config = $this->configHandler->get($boardId);

        $skinSection = new SkinSection(BoardModule::getId(), $boardId);

        return $this->presenter->make('module.skin', [
            'config' => $config,
            'boardId' => $boardId,
            'skinSection' => $skinSection,
        ]);
    }

    /**
     * edit editor
     *
     * @param string $boardId board id
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function editEditor($boardId)
    {
        $config = $this->configHandler->get($boardId);

        $editorSection = new EditorSection($boardId);

        return $this->presenter->make('module.editor', [
            'config' => $config,
            'boardId' => $boardId,
            'editorSection' => $editorSection,
        ]);
    }

    /**
     * edit columns
     *
     * @param string $boardId board id
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     *
     * @deprecated instead use ColumnSetting or BoardSettingsController@editConfig
     */
    public function editColumns($boardId)
    {
        $config = $this->configHandler->get($boardId);

        $sortListColumns = $this->configHandler->getSortListColumns($config);
        $sortFormColumns = $this->configHandler->getSortFormColumns($config);

        return $this->presenter->make('module.columns', [
            'boardId' => $boardId,
            'sortListColumns' => $sortListColumns,
            'sortFormColumns' => $sortFormColumns,
            'config' => $config,
        ]);
    }

    /**
     * update columns
     *
     * @param Request $request request
     * @param string  $boardId board id
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @deprecated instead use ColumnSetting or BoardSettingsController@editConfig
     */
    public function updateColumns(Request $request, $boardId)
    {
        $config = $this->configHandler->get($boardId);
        $inputs = $request->except('_token');
        foreach ($inputs as $key => $value) {
            $config->set($key, $value);
        }

        $this->instanceManager->updateConfig($config->getPureAll());

        return redirect()->to($this->urlHandler->managerUrl('columns', ['boardId' => $boardId]));
    }

    /**
     * edit dynamic field
     *
     * @param string $boardId board id
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function editDynamicField($boardId)
    {
        $config = $this->configHandler->get($boardId);

        $dynamicFieldSection = new DynamicFieldSection(
            $config->get('documentGroup'),
            XeDB::connection(),
            $config->get('revision')
        );
        return $this->presenter->make('module.dynamicField', [
            'boardId' => $boardId,
            'dynamicFieldSection' => $dynamicFieldSection,
        ]);
    }

    /**
     * edit toggle menu
     *
     * @param string $boardId board id
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function editToggleMenu($boardId)
    {
        $toggleMenuSection = new ToggleMenuSection(BoardModule::getId(), $boardId);

        return $this->presenter->make('module.toggleMenu', [
            'boardId' => $boardId,
            'toggleMenuSection' => $toggleMenuSection,
        ]);
    }

    /**
     * document manager
     *
     * @param Request         $request         request
     * @param RouteRepository $routeRepository route repository
     * @return \Xpressengine\Presenter\Presentable
     */
    public function docsIndex(Request $request, RouteRepository $routeRepository)
    {
        $request->flash();

        $instances = [];
        $instanceIds = [];
        $urls = [];
        $instanceRoutes = $routeRepository->fetchByModule(BoardModule::getId());
        foreach ($instanceRoutes as $route) {
            $instanceIds[] = $route->instance_id;
            $urls[$route->instance_id] = $route->url;

            $instances[] = [
                'id' => $route->instance_id,
                'name' => $route->url,
            ];
        }

        $titles = [];
        $menus = MenuItem::whereIn('id', $instanceIds)->get();
        foreach ($menus as $menu) {
            $titles[$menu->id] = xe_trans($menu->title);
        }

        $query = Board::whereIn('instance_id', $instanceIds)->where('status', '<>', Board::STATUS_TRASH);

        $totalCount = $query->count();

        $query = $this->makeWhere($query, $request);
        $query->orderBy('created_at', 'desc');
        $documents = $query->paginate(15)->appends($request->except('page'));

        $searchTargetWord = $request->get('search_target');
        if ($request->get('search_target') == 'pure_content') {
            $searchTargetWord = 'content';
        } elseif ($request->get('search_target') == 'title_pure_content') {
            $searchTargetWord = 'titleAndContent';
        }

        $stateMessage = xe_trans('board::manage.state');
        if ($state = $request->get('search_state')) {
            $stateMessage = self::getStateMessage($state);
        }

        $boards = MenuItem::where('type', 'board@board')->pluck('title', 'id')->toArray();

        $boardSearchMessage = xe_trans('board::manage.targetBoard');
        if ($boardId = $request->get('search_board', 'board::manage.targetBoard')) {
            if (array_key_exists($boardId, $boards)) {
                $boardSearchMessage = xe_trans($boards[$boardId]);
            } else {
                $boardSearchMessage = xe_trans($boardId);
            }
        }

        return $this->presenter->make(
            'docs.index',
            compact(
                'documents',
                'instances',
                'urls',
                'titles',
                'searchTargetWord',
                'stateMessage',
                'totalCount',
                'boards',
                'boardSearchMessage'
            )
        );
    }

    /**
     * get state message
     * @param string $state state
     *
     * @return string
     */
    protected function getStateMessage($state)
    {
        list($searchField, $searchValue) = explode('|', $state);

        $stateMessage = 'board::manage.stateFilter.';

        if ($searchField == 'display') {
            switch ($searchValue) {
                case Board::DISPLAY_VISIBLE:
                    $stateMessage .= 'public';
                    break;

                case Board::DISPLAY_SECRET:
                    $stateMessage .= 'secret';
                    break;
            }
        } elseif ($searchField == 'approved') {
            switch ($searchValue) {
                case Board::APPROVED_APPROVED:
                    $stateMessage .= 'approve';
                    break;

                case Board::APPROVED_WAITING:
                    $stateMessage .= 'waiting';
                    break;

                case Board::APPROVED_REJECTED:
                    $stateMessage .= 'reject';
                    break;
            }
        } elseif ($searchField == 'status') {
            switch ($searchValue) {
                case Board::STATUS_NOTICE:
                    $stateMessage .= 'notice';
                    break;
            }
        }

        $stateMessage = xe_trans($stateMessage);

        return $stateMessage;
    }

    /**
     * document manager
     *
     *
     * @param Request         $request         request
     * @param RouteRepository $routeRepository route repository
     * @return \Xpressengine\Presenter\Presentable
     */
    public function docsTrash(Request $request, RouteRepository $routeRepository)
    {
        $instances = [];
        $instanceIds = [];
        $urls = [];

        $instanceRoutes = $routeRepository->fetchByModule(BoardModule::getId());
        foreach ($instanceRoutes as $route) {
            $instanceIds[] = $route->instance_id;
            $urls[$route->instance_id] = $route->url;
            $instances[] = [
                'id' => $route->instance_id,
                'name' => $route->url,
            ];
        }

        $titles = [];
        $menus = MenuItem::whereIn('id', $instanceIds)->get();
        foreach ($menus as $menu) {
            $titles[$menu->id] = xe_trans($menu->title);
        }

        $query = Board::whereIn('instance_id', $instanceIds)
            ->whereIn('status', [Board::STATUS_TRASH, Board::STATUS_TRASH_NOTICE]);
        $query = $this->makeWhere($query, $request);
        $query->orderBy('created_at', 'desc');
        $documents = $query->paginate(15)->appends($request->except('page'));

        $searchTargetWord = $request->get('search_target');
        if ($request->get('search_target') == 'pure_content') {
            $searchTargetWord = 'content';
        } elseif ($request->get('search_target') == 'title_pure_content') {
            $searchTargetWord = 'titleAndContent';
        }
        return $this->presenter->make(
            'docs.trash',
            compact(
                'documents',
                'instances',
                'urls',
                'titles',
                'searchTargetWord'
            )
        );
    }

    /**
     * update document approve status
     *
     * @param Request $request request
     * @return \Illuminate\Http\RedirectResponse|Redirect
     */
    public function approve(Request $request)
    {
        $approved = $request->get('approved');

        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = Board::find($documentIds);

        switch ($approved) {
            case 'approved':
                $method = 'approveSetApprove';
                break;
            case 'rejected':
                $method = 'approveSetReject';
                break;
            default:
                throw new InvalidArgumentException;
                break;
        }

        foreach ($items as $item) {
            $this->handler->$method($item);
        }

        return $this->presenter->makeApi([]);
    }

    /**
     * destroy document
     *
     * @param Request $request request
     * @return \Illuminate\Http\RedirectResponse|Redirect
     * @throws \Exception
     */
    public function destroy(Request $request)
    {
        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $this->handler->remove($item, $this->configHandler->get($item->instance_id));
        }

        Session::flash('alert', ['type' => 'success', 'message' => xe_trans('xe::processed')]);

        return $this->presenter->makeApi([]);
    }

    /**
     * move to trash
     *
     * @param Request $request request
     * @return \Illuminate\Http\RedirectResponse|Redirect
     * @throws \Exception
     */
    public function trash(Request $request)
    {
        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $this->handler->trash($item, $this->configHandler->get($item->instance_id));
        }

        Session::flash('alert', ['type' => 'success', 'message' => xe_trans('xe::processed')]);

        return $this->presenter->makeApi([]);
    }

    /**
     * move to restore
     *
     * @param Request $request request
     * @return \Illuminate\Http\RedirectResponse|Redirect
     * @throws \Exception
     */
    public function restore(Request $request)
    {
        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $this->handler->restore($item, $this->configHandler->get($item->instance_id));
        }

        return $this->presenter->makeApi([]);
    }

    /**
     * move to move
     *
     * @param Request $request request
     * @return \Illuminate\Http\RedirectResponse|Redirect
     * @throws \Exception
     */
    public function move(Request $request)
    {
        $currentInstanceId = $request->get('current_instance_id', '');
        if ($currentInstanceId != '') {
            $currentUrl = $this->urlHandler->get('index', [], $currentInstanceId);
        } else {
            $currentUrl = '/';
        }

        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $instanceId = $request->get('instance_id');
        $dstConfig = $this->configHandler->get($instanceId);
        if ($dstConfig === null) {
            throw new NotFoundConfigHttpException(['instanceId' => $instanceId]);
        }

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $originConfig = $this->configHandler->get($item->instance_id);
            $this->handler->move($item, $dstConfig, $originConfig);
        }

        Session::flash('alert', ['type' => 'success', 'message' => xe_trans('xe::processed')]);

        return $this->presenter->makeApi(['currentUrl' => $currentUrl]);
    }

    /**
     * move to copy
     *
     * @param Request $request request
     * @return \Illuminate\Http\RedirectResponse|Redirect
     * @throws \Exception
     */
    public function copy(Request $request)
    {
        $documentIds = $request->get('id');
        $documentIds = is_array($documentIds) ? $documentIds : [$documentIds];

        $instanceId = $request->get('instance_id');
        $config = $this->configHandler->get($instanceId);
        if ($config === null) {
            throw new NotFoundConfigHttpException(['instanceId' => $instanceId]);
        }

        $items = Board::find($documentIds);

        foreach ($items as $item) {
            $user = new Guest;
            if ($item->user_id != '') {
                $user = User::find($item->user_id);
            }

            $this->handler->copy($item, $user, $config);
        }

        Session::flash('alert', ['type' => 'success', 'message' => xe_trans('xe::processed')]);

        return $this->presenter->makeApi([]);
    }

    /**
     * make where
     * @param Board   $query   query
     * @param Request $request request
     *
     * @return mixed
     */
    protected function makeWhere($query, $request)
    {
        //기간 검색
        if ($startDate = $request->get('start_date')) {
            $query = $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate = $request->get('end_date')) {
            $query = $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        //검색어 검색
        if ($request->get('search_target') == 'title') {
            $query = $query->where(
                'title',
                'like',
                sprintf('%%%s%%', implode('%', explode(' ', $request->get('search_keyword'))))
            );
        }
        if ($request->get('search_target') == 'pure_content') {
            $query = $query->where(
                'pure_content',
                'like',
                sprintf('%%%s%%', implode('%', explode(' ', $request->get('search_keyword'))))
            );
        }
        if ($request->get('search_target') == 'title_pure_content') {
            $query = $query->whereNested(function ($query) use ($request) {
                $query->where(
                    'title',
                    'like',
                    sprintf('%%%s%%', implode('%', explode(' ', $request->get('search_keyword'))))
                )->orWhere(
                    'pure_content',
                    'like',
                    sprintf('%%%s%%', implode('%', explode(' ', $request->get('search_keyword'))))
                );
            });
        }

        if ($request->get('search_target') == 'writer') {
            $query = $query->where('writer', 'like', sprintf('%%%s%%', $request->get('search_keyword')));
        }

        //작성자 ID 검색
        if ($request->get('search_target') == 'writerId') {
            $writerId = $request->get('search_keyword');

            $writers = \XeUser::where(function($query) use($writerId) {
                return $query
                    ->where('email', 'like', '%' . $writerId . '%')
                    ->orWhere('login_id', 'like', '%' . $writerId . '%');
            })->get();

            $query = $query->whereIn('user_id', $writers->pluck('id')->toArray());
        }

        //필터 검색
        if ($state = $request->get('search_state')) {
            list($searchField, $searchValue) = explode('|', $state);

            $query->where($searchField, $searchValue);
        }

        //게시판 검색
        if ($targetBoard = $request->get('search_board')) {
            $query->where('instance_id', $targetBoard);
        }

        return $query;
    }
}
