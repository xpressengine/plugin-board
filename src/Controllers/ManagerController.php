<?php
/**
 * ManagerController
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Controllers;

use XeDB;
use Redirect;
use Presenter;
use App\Http\Controllers\Controller;
use App\Sections\DynamicFieldSection;
use App\Sections\ToggleMenuSection;
use App\Sections\SkinSection;
use Xpressengine\Category\CategoryHandler;
use Xpressengine\Http\Request;
use Xpressengine\Permission\Grant;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\InstanceManager;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Modules\Board as BoardModule;

/**
 * ManagerController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
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
     * create instance
     */
    public function __construct(Handler $handler, ConfigHandler $configHandler, UrlHandler $urlHandler, InstanceManager $instanceManager)
    {
        /** @var \Xpressengine\Plugins\Board\Plugin $plugin */
        //$this->plugin = app('xe.plugin')->getPlugin('board')->getObject();

        $this->handler = $handler;
        $this->configHandler = $configHandler;
        $this->urlHandler = $urlHandler;

        $this->instanceManager =  $instanceManager;

        $this->presenter = app('xe.presenter');

        $this->presenter->setSettingsSkin(BoardModule::getId());
        $this->presenter->share('handler', $this->handler);
        //$this->presenter->share('permissionHandler', $this->permissionHandler);
        $this->presenter->share('configHandler', $this->configHandler);
        $this->presenter->share('urlHandler', $this->urlHandler);
    }

    /**
     * edit
     *
     * @param BoardPermissionHandler $boardPermission
     * @param string $boardId board id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function edit(BoardPermissionHandler $boardPermission, $boardId)
    {
        $config = $this->configHandler->get($boardId);

        $listOptions = $this->configHandler->listColumns($boardId);
        $listColumns = $config->get('listColumns');

        // 현재 선택된건 제외 시키고 보여줌
        $listOptions = array_diff($listOptions, $listColumns);

        $formColumns = $this->configHandler->formColumns($boardId);

        $skinSection = (new SkinSection())->setting(BoardModule::getId(), $boardId);

        $commentSection = '';
        //$commentSection = (new CommentSection())->setting($boardId);

        $dynamicFieldSection = (new DynamicFieldSection($config->get('documentGroup')))
            ->setting(XeDB::connection(), $config->get('revision'));

        $toggleMenuSection = (new ToggleMenuSection())->setting(BoardModule::getId(), $boardId);

        $perms = $boardPermission->getPerms($boardId);

        return $this->presenter->make('edit', [
            'config' => $config,
            'boardId' => $boardId,
            'listOptions' => $listOptions,
            'listColumns' => $listColumns,
            'formColumns' => $formColumns,
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
     * @param Request $request
     * @param BoardPermissionHandler $boardPermission
     * @param string $boardId board id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, BoardPermissionHandler $boardPermission, $boardId)
    {
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

        $config = $this->instanceManager->updateConfig($config->getPureAll());

        // permission update
        $grant = new Grant();

        foreach ($boardPermission->getActions() as $action) {
            $permInputs = $request->only($permissionNames[$action]);
            if ($permInputs[$action.'Mode'] == 'manual') {
                $grant = $boardPermission->createGrant($grant, $action, [
                    Grant::RATING_TYPE => $permInputs[$action . 'Rating'],
                    Grant::GROUP_TYPE => isset($permInputs[$action . 'Group']) ?
                        $permInputs[$action . 'Group'] : [],
                    Grant::USER_TYPE => explode(',', $permInputs[$action . 'User']),
                    Grant::EXCEPT_TYPE => explode(',', $permInputs[$action . 'Except'])
                ]);
            }
        }

        $boardPermission->set($boardId, $grant);

        return redirect()->to($this->urlHandler->managerUrl('edit', ['boardId' => $boardId]));
    }

    public function storeCategory(CategoryHandler $categoryHandler, $boardId)
    {
        $input = [
            'name' => $boardId . '-' . BoardModule::getId(),
        ];
        $category = $categoryHandler->create($input);

        $config = $this->configHandler->get($boardId);
        $config->set('categoryId', $category->id);
        $this->instanceManager->updateConfig($config->getPureAll());

        return Presenter::makeApi(
            $category->getAttributes()
        );
    }
}
