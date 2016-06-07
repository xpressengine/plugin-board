<?php
/**
 * DefaultSkin
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
namespace Xpressengine\Plugins\Board\Skins;

use XePresenter;

use Xpressengine\Config\ConfigEntity;
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
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class DefaultSkin extends AbstractSkin
{
    protected static $skinAlias = 'board::views.defaultSkin';

    /**
     * @var array
     */
    protected $defaultListColumns = [
        'title', 'writer', 'createdAt', 'assentCount', 'dissentCount', 'readCount', 'updatedAt',
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

        // 기본 설정
        if (empty($this->config['listColumns'])) {
            $this->config['listColumns'] = $this->defaultSelectedListColumns;
        }
        if (empty($this->config['formColumns'])) {
            $this->config['formColumns'] = $this->defaultSelectedFormColumns;
        }
        $this->data['skinConfig'] = $this->config;


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
        $this->setDynamicFieldSkins();
    }

    /**
     * create customizer
     *
     * @return void
     */
    protected function editCustomizer()
    {
        $this->setDynamicFieldSkins();
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

            $boardList[] = [
                'value' => $config->get('boardId'),
                'text' => $config->get('boardName'),
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

        return View::make(
            sprintf('%s.%s', static::$skinAlias, 'setting'),
            [
                'sortListColumns' => $this->getSortListColumns($config, 'aacad4df'),
                'sortFormColumns' => $this->getSortFormColumns($config, 'aacad4df'),
                'config' => $config
            ]
        );
    }

    protected function getSortListColumns(array $config, $instanceId)
    {
        /** @var \Xpressengine\Plugins\Board\ConfigHandler $configHandler */
        $configHandler = app('xe.board.config');

        if (empty($config['sortListColumns'])) {
            $sortListColumns = $this->defaultSelectedListColumns;
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
            $sortFormColumns = $this->defaultSelectedFormColumns;
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
