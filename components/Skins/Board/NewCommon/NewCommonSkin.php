<?php
/**
 * NewCommonSkin
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2020 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Components\Skins\Board\NewCommon;

use Xpressengine\Permission\Instance;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\GenericBoardSkin;
use View;
use Gate;
use XeFrontend;
use XeRegister;
use XePresenter;
Use XeSkin;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugins\Board\Components\DynamicFields\Category\Skins\DesignSelect\DesignSelectSkin;
use Xpressengine\Presenter\Presenter;
use Xpressengine\Routing\InstanceConfig;

/**
 * NewCommonSkin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2020 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class NewCommonSkin extends GenericBoardSkin
{
    protected static $path = 'board/components/Skins/Board/NewCommon';

    /**
     * render
     *
     * @return \Illuminate\Contracts\Support\Renderable|string
     */
    public function render()
    {
        $this->setSkinConfig();
        $this->setDynamicFieldSkins();
        $this->setPaginationPresenter();

        if ($this->isManager()) {
            $this->setBoardList();
        }

        // 스킨 view(blade)파일이나 js 에서 사용할 다국어 정의
        XeFrontend::translation([
            'board::selectPost',
            'board::selectBoard',
            'board::msgDeleteConfirm',
        ]);

        // set skin path
        $this->data['_skinPath'] = static::$path;
        $this->data['isManager'] = $this->isManager();

        /**
         * If view file is not 'index.blade.php' then change view path to CommonSkin's path.
         * CommonSkin extends by other Skins. Extended Skin can make just 'index.blade.php'
         * and other blade files will use to CommonSkin's blade files.
         */
//        if ($this->view != 'index') {
//            static::$path = self::$path;
//        }
        /**
         * If view file is not exists to extended skin component then change view path to CommonSkin's path.
         * CommonSkin extends by other Skins. Extended Skin can make own blade files.
         * If not make blade file then use to CommonSkin's blade files.
         */
        if (View::exists(sprintf('%s/views/%s', static::$path, $this->view)) == false) {
            static::$path = self::$path;
        }

        $contentView = parent::render();

        /**
         * If render type is not for Presenter::RENDER_CONTENT
         * then use CommonSkin's '_frame.blade.php' for layout.
         * '_frame.blade.php' has assets load script like js, css.
         */
        if (XePresenter::getRenderType() == Presenter::RENDER_CONTENT) {
            $view = $contentView;
        } else {
            // wrapped by _frame.blade.php
            if (View::exists(sprintf('%s/views/_frame', static::$path)) === false) {
                static::$path = self::$path;
            }
            $view = View::make(sprintf('%s/views/_frame', static::$path), $this->data);
            $view->content = $contentView;
        }

        return $view;
    }

    public function renderSetting(array $config = [])
    {
        /** @var ConfigHandler $configHandler */
        $configHandler = app('xe.board.config');
        
//        TODO instanceId 개선 확인
        $arr = explode(':', request()->get('instanceId'));
        $instanceId = $arr[1];
        
        $boardConfig = $configHandler->get($instanceId);
        
        $config['boardConfig'] = $boardConfig;
        $config['listColumns'] = $configHandler->getSortListColumns($boardConfig);
        
        $dynamicFields = [];
        $fieldTypes = $configHandler->getDynamicFields($boardConfig);
        foreach ($fieldTypes as $fieldType) {
            $dynamicFields[$fieldType->get('id')] = $fieldType;
        }
        $config['dynamicFields'] = $dynamicFields;
        
        return parent::renderSetting($config);
    }

    /**
     * set skin config to data
     *
     * @return void
     */
    protected function setSkinConfig()
    {
        $this->config['formColumns'] = $this->data['config']->get('formColumns');
        
//        TODO skinConfig 스킨 변수로 변경
        $this->data['skinConfig'] = $this->config;
    }

    /**
     * replace dynamicField skins
     *
     * @return void
     */
    protected function setDynamicFieldSkins()
    {
        // replace dynamicField skin registered information
        XeRegister::set('fieldType/xpressengine@Category/fieldSkin/xpressengine@default', DesignSelectSkin::class);
    }

    /**
     * set pagination presenter
     *
     * @return void
     * @see views/defaultSkin/index.blade.php
     */
    protected function setPaginationPresenter()
    {
        if (isset($this->data['paginate'])) {
            $this->data['paginate']->setPath($this->data['urlHandler']->get('index'));
        }
    }

    /**
     * set board list (for supervisor)
     *
     * @return void
     */
    protected function setBoardList()
    {
        $instanceConfig = InstanceConfig::instance();
        $instanceId = $instanceConfig->getInstanceId();

        $configHandler = app('xe.board.config');
        $boards = $configHandler->gets();
        $boardIds = [];

        /** @var ConfigEntity $config */
        foreach ($boards as $config) {
            // 현재의 게시판은 리스트에서 제외
            if ($instanceId === $config->get('boardId')) {
                continue;
            }
            $boardIds[] = $config->get('boardId');
        }

        $menuItems = MenuItem::whereIn('id', $boardIds)->get();
        $menuItemMap = [];
        foreach ($menuItems as $menuItem) {
            $menuItemMap[$menuItem->id] = $menuItem;
        }

        $boardList = [];
        foreach ($boards as $config) {
            if ($instanceId === $config->get('boardId')) {
                continue;
            }

            $title = '';
            if (isset($menuItemMap[$config->get('boardId')])) {
                $menuItem = $menuItemMap[$config->get('boardId')];
                $title = xe_trans($menuItem->title);
            }

            $boardName = $config->get('boardName');
            if ($boardName) {
                $boardName = xe_trans($boardName);
                if ($boardName != '') {
                    $title = sprintf('%s(%s)', $title, $boardName);
                }
            }

            $boardList[] = [
                'value' => $config->get('boardId'),
                'text' => '_custom_::' . $title,
            ];
        }
        $this->data['boardList'] = $boardList;
    }

    /**
     * is manager
     *
     * @return bool
     */
    protected function isManager()
    {
        $boardPermission = app('xe.board.permission');
        return isset($this->data['instanceId']) && Gate::allows(
            BoardPermissionHandler::ACTION_MANAGE,
            new Instance($boardPermission->name($this->data['instanceId']))
        );
    }
}
