<?php
/**
 * XEDefault
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

namespace Xpressengine\Plugins\Board\Components\Skins\Board\XEDefault;

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
use Xpressengine\Plugins\Board\Components\DynamicFields\Category\Skins\DesignSelect\DesignSelectSkin;
use Xpressengine\Presenter\Presenter;

/**
 * XEDefault
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2020 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class XEDefault extends GenericBoardSkin
{
    protected static $path = 'board/components/Skins/Board/XEDefault';

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
        
        // set skin path
        $this->data['_skinPath'] = static::$path;
        $this->data['isManager'] = $this->isManager();

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
        $config['sortListColumns'] = $configHandler->getSortListColumns($boardConfig);
        $config['listColumns'] = $boardConfig->get('listColumns');
        
        $dynamicFields = [];
        $fieldTypes = $configHandler->getDynamicFields($boardConfig);
        foreach ($fieldTypes as $fieldType) {
            $dynamicFields[$fieldType->get('id')] = $fieldType;
        }
        $config['dynamicFields'] = $dynamicFields;
        
        return parent::renderSetting($config);
    }
    
    public function resolveSetting(array $inputs = [])
    {
        if (isset($inputs['visibleIndexMobileWriteButton']) === false) {
            $inputs['visibleIndexMobileWriteButton'] = '';
        }

        if (isset($inputs['visibleIndexDefaultProfileImage']) === false) {
            $inputs['visibleIndexDefaultProfileImage'] = '';
        }
        
        if (isset($inputs['visibleShowProfileImage']) === false) {
            $inputs['visibleShowProfileImage'] = '';
        }

        if (isset($inputs['visibleShowDisplayName']) === false) {
            $inputs['visibleShowDisplayName'] = '';
        }

        if (isset($inputs['visibleShowReadCount']) === false) {
            $inputs['visibleShowReadCount'] = '';
        }

        if (isset($inputs['visibleShowCreatedAt']) === false) {
            $inputs['visibleShowCreatedAt'] = '';
        }
        
        return parent::resolveSetting($inputs);
    }

    /**
     * set skin config to data
     *
     * @return void
     */
    protected function setSkinConfig()
    {
        if (isset($this->config['listColumns']) === true) {
            $this->config['skinListColumns'] = $this->config['listColumns'];
        } else {
            $this->config['skinListColumns'] = $this->data['config']->get('listColumns');
        }

        $this->config['formColumns'] = $this->data['config']->get('formColumns');
        $this->config['listColumns'] = $this->data['config']->get('listColumns');
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
