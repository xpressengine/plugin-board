<?php
/**
 * DefaultSkin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Skins;

use XePresenter;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugins\Board\Skins\DynamicField\DesignSelectSkin;
use Xpressengine\Plugins\Board\Skins\PaginationMobilePresenter;
use Xpressengine\Plugins\Board\Skins\PaginationPresenter;
use Xpressengine\Presenter\Presenter;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\Skin\AbstractSkin;
use View;

/**
 * DefaultSkin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 */
class DefaultSkin extends AbstractSkin
{
    protected static $skinAlias = 'board::views.defaultSkin';

    /**
     * @var array
     */
    protected $defaultListColumns = [
        'title', 'writer', 'assentCount', 'readCount', 'createdAt', 'updatedAt', 'dissentCount',
    ];

    protected $defaultSelectedListColumns = [
        'title', 'writer',  'assentCount', 'readCount', 'createdAt',
    ];

    /**
     * @var array
     */
    protected $defaultFormColumns = [
        'title', 'content',
    ];

    /**
     * @var array
     */
    protected $defaultSelectedFormColumns = [
        'title', 'content',
    ];

    /**
     * render
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // call customizer
        // view 아이디를 기준으로 Customizer 호출
        $customizer = $this->view . 'Customizer';
        if (method_exists($this, $customizer)) {
            $this->$customizer();
        }

        $this->data['skinAlias'] = static::$skinAlias;

        $contentView = View::make(
            sprintf('%s.%s', static::$skinAlias, $this->view),
            $this->data
        );
        if (XePresenter::getRenderType() == Presenter::RENDER_CONTENT) {
            $view = $contentView;
        } else {
            // wrapped by _frame.blade.php
            $view = View::make(sprintf('%s._frame', static::$skinAlias), $this->data);
            $view->content = $contentView->render();
        }

        return $view;
    }

    /**
     * get manage URI
     *
     * @return string
     */
    public static function getSettingsURI()
    {
    }

    /**
     * index customizer
     *
     * @return void
     */
    protected function indexCustomizer()
    {
        $this->setSkinConfig();
        $this->setDynamicFieldSkins();
        $this->setPaginationPresenter();
        $this->setBoardList();
    }

    /**
     * show customizer
     *
     * @return void
     */
    protected function showCustomizer()
    {
        $this->setSkinConfig();
        $this->setDynamicFieldSkins();
        $this->setPaginationPresenter();
        $this->setBoardList();
    }

    /**
     * create customizer
     *
     * @return void
     */
    protected function createCustomizer()
    {
        $this->setSkinConfig();
        $this->setDynamicFieldSkins();
    }

    /**
     * create customizer
     *
     * @return void
     */
    protected function editCustomizer()
    {
        $this->setSkinConfig();
        $this->setDynamicFieldSkins();
    }

    /**
     * set skin config to data
     *
     * @return void
     */
    protected function setSkinConfig()
    {
        // 기본 설정
        if (empty($this->config['listColumns'])) {
            $this->config['listColumns'] = $this->defaultSelectedListColumns;
        }
        if (empty($this->config['formColumns'])) {
            $this->config['formColumns'] = $this->defaultSelectedFormColumns;
        }
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
        /** @var \Xpressengine\Register\Container $register */
        $register = app('xe.register');
        $register->set('FieldType/xpressengine@Category/FieldSkin/xpressengine@default', DesignSelectSkin::class);
    }

    /**
     * set pagination presenter
     *
     * @return void
     */
    protected function setPaginationPresenter()
    {
        $this->data['paginate']->setPath($this->data['urlHandler']->get('index'));
        $this->data['paginationPresenter'] = new PaginationPresenter($this->data['paginate']);
        $this->data['paginationMobilePresenter'] = new PaginationMobilePresenter($this->data['paginate']);
    }

    /**
     * set board list
     *
     * @return void
     */
    protected function setBoardList()
    {
        $instanceConfig = InstanceConfig::instance();
        $instanceId = $instanceConfig->getInstanceId();

        $configHandler = app('xe.board.config');
        $boards = $configHandler->gets();
        $boardList = [];
        /** @var ConfigEntity $config */
        foreach ($boards as $config) {
            // 현재의 게시판은 리스트에서 제외
            if ($instanceId === $config->get('boardId')) {
                continue;
            }

            $boardName = $config->get('boardName');
            if ($boardName === null || $boardName === '') {
                $menuItem = MenuItem::find($config->get('boardId'));
                $boardName = $menuItem->title;
            }

            $boardList[] = [
                'value' => $config->get('boardId'),
                'text' => $boardName,
            ];
        }
        $this->data['boardList'] = $boardList;
    }

    /**
     * get setting view
     *
     * @param array $config config
     * @return \Illuminate\Contracts\Support\Renderable|string
     */
    public function getSettingView($config = [])
    {
        if ($config === []) {
            $config = [
                'listColumns' => $this->defaultSelectedListColumns,
                'formColumns' => $this->defaultSelectedFormColumns,
            ];
        }

        $arr = explode(':', request()->get('instanceId'));
        $instanceId = $arr[1];

        return View::make(
            sprintf('%s.%s', static::$skinAlias, 'setting'),
            [
                'sortListColumns' => $this->getSortListColumns($config, $instanceId),
                'sortFormColumns' => $this->getSortFormColumns($config, $instanceId),
                'config' => $config
            ]
        );
    }

    protected function getSortListColumns(array $config, $instanceId)
    {
        /** @var \Xpressengine\Plugins\Board\ConfigHandler $configHandler */
        $configHandler = app('xe.board.config');

        if (empty($config['sortListColumns'])) {
            $sortListColumns = $this->defaultListColumns;
        } else {
            $sortListColumns = $config['sortListColumns'];
        }

        $dynamicFields = $configHandler->getDynamicFields($configHandler->get($instanceId));
        $currentDynamicFields = [];
        /**
         * @var ConfigEntity $dynamicFieldConfig
         */
        foreach ($dynamicFields as $dynamicFieldConfig) {
            if ($dynamicFieldConfig->get('use') === true) {
                $currentDynamicFields[] = $dynamicFieldConfig->get('id');
            }

            if (
                $dynamicFieldConfig->get('use') === true &&
                in_array($dynamicFieldConfig->get('id'), $sortListColumns) === false
            ) {
                $sortListColumns[] = $dynamicFieldConfig->get('id');
            }
        }

        $usableColumns = array_merge($this->defaultListColumns, $currentDynamicFields);
        foreach ($sortListColumns as $index => $column) {
            if (in_array($column, $usableColumns) === false) {
                unset($sortListColumns[$index]);
            }
        }

        return $sortListColumns;
    }

    protected function getSortFormColumns(array $config, $instanceId)
    {
        /** @var \Xpressengine\Plugins\Board\ConfigHandler $configHandler */
        $configHandler = app('xe.board.config');

        if (empty($config['sortFormColumns'])) {
            $sortFormColumns = $this->defaultFormColumns;
        } else {
            $sortFormColumns = $config['sortFormColumns'];
        }
        $dynamicFields = $configHandler->getDynamicFields($configHandler->get($instanceId));
        $currentDynamicFields = [];
        /**
         * @var ConfigEntity $dynamicFieldConfig
         */
        foreach ($dynamicFields as $dynamicFieldConfig) {
            if ($dynamicFieldConfig->get('use') === true) {
                $currentDynamicFields[] = $dynamicFieldConfig->get('id');
            }

            if (
                $dynamicFieldConfig->get('use') === true &&
                in_array($dynamicFieldConfig->get('id'), $sortFormColumns) === false
            ) {
                $sortFormColumns[] = $dynamicFieldConfig->get('id');
            }
        }

        $usableColumns = array_merge($this->defaultFormColumns, $currentDynamicFields);
        foreach ($sortFormColumns as $index => $column) {
            if (in_array($column, $usableColumns) === false) {
                unset($sortFormColumns[$index]);
            }
        }

        return $sortFormColumns;
    }
}
