<?php
namespace Xpressengine\Plugins\Board\Components\Skins\Board\Common;

use Xpressengine\DynamicField\ColumnEntity;
use Xpressengine\DynamicField\DynamicFieldHandler;
use Xpressengine\Permission\Instance;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
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

class CommonSkin extends GenericBoardSkin
{
    protected static $path = 'board/components/Skins/Board/Common';

    /**
     * @var array
     * @deprecated beta.24.
     */
    protected $defaultListColumns = [
        'title', 'writer', 'assent_count', 'read_count', 'created_at', 'updated_at', 'dissent_count',
    ];

    /**
     * @var array
     * @deprecated beta.24.
     */
    protected $defaultSelectedListColumns = [
        'title', 'writer',  'assent_count', 'read_count', 'created_at',
    ];

    /**
     * @var array
     * @deprecated beta.24.
     */
    protected $defaultFormColumns = [
        'title', 'content',
    ];

    /**
     * @var array
     * @deprecated beta.24.
     */
    protected $defaultSelectedFormColumns = [
        'title', 'content',
    ];


    /**
     * intercept DynamicField 업데이트
     *
     * beta.24. 정렬 기능을 게시판 고유 기능으로 변경
     *
     * @return void
     * @deprecated beta.24. use Plugin\Resources::interceptDynamicField() instead
     */
    public static function interceptDynamicField()
    {
        intercept(
            DynamicFieldHandler::class . '@create',
            'board@commonSkin::createDynamicField',
            function ($func, ConfigEntity $config, ColumnEntity $column = null) {
                $func($config, $column);

                // remove prefix name of group
                $instanceId = str_replace('documents_', '', $config->get('group'));

                /** @var \Xpressengine\Plugins\Board\ConfigHandler $configHandler */
                $configHandler = app('xe.board.config');
                $boardConfig = $configHandler->get($instanceId);
                if ($boardConfig !== null) {
                    $skinInstanceId = sprintf('%s:%s', BoardModule::getId(), $instanceId);
                    $skinId = static::getId();

                    /** @var \Xpressengine\Skin\GenericSkin $skin */
                    $skin = XeSkin::get($skinId);

                    $skinConfig = XeSkin::getStore()->getConfigs($skinInstanceId, $skinId);

                    $skinInstance = new static;
                    $skinConfig['formColumns'] = $skinInstance->getSortFormColumns($skinConfig, $instanceId);

                    $skinConfig = $skin->resolveSetting($skinConfig);
                    $skin->setting($skinConfig);
                    XeSkin::saveConfig($skinInstanceId, $skin);
                }
            }
        );
    }

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
        $this->setBoardList();
        $this->setTerms();

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
        if ($this->view != 'index') {
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
            $view = View::make(sprintf('%s/views/_frame', CommonSkin::$path), $this->data);
            $view->content = $contentView;
        }

        return $view;
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
            $this->config['listColumns'] = $this->data['config']->get('listColumns');
        }
        if (empty($this->config['formColumns'])) {
            $this->config['formColumns'] = $this->data['config']->get('formColumns');
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
        $boardList = [];
        /** @var ConfigEntity $config */
        foreach ($boards as $config) {
            // 현재의 게시판은 리스트에서 제외
            if ($instanceId === $config->get('boardId')) {
                continue;
            }

            $title = $config->get('boardId');
            $menuItem = MenuItem::find($config->get('boardId'));
            if ($menuItem) {
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
                'text' => $title,
            ];
        }
        $this->data['boardList'] = $boardList;
    }

    /**
     * set terms for search select box list
     *
     * @return array
     */
    protected function setTerms()
    {
        $this->data['terms'] = [
            ['value' => '1week', 'text' => 'board::1week'],
            ['value' => '2week', 'text' => 'board::2week'],
            ['value' => '1month', 'text' => 'board::1month'],
            ['value' => '3month', 'text' => 'board::3month'],
            ['value' => '6month', 'text' => 'board::6month'],
            ['value' => '1year', 'text' => 'board::1year'],
        ];
    }

    /**
     * get setting view
     *
     * @param array $config board config
     * @return \Illuminate\Contracts\Support\Renderable|string
     */
    public function renderSetting(array $config = [])
    {
        /**
         * beta.24
         * 스킨 설정의 컬럼 정보 수정 기능은 게시판 컬럼 설정으로 이동
         *
         */
//        if (static::class == self::class) {
//            if ($config === []) {
//                $config = [
//                    'listColumns' => $this->defaultSelectedListColumns,
//                    'formColumns' => $this->defaultSelectedFormColumns,
//                ];
//            }
//
//            $arr = explode(':', request()->get('instanceId'));
//            $instanceId = $arr[1];
//
//            return View::make(sprintf('%s/views/setting', CommonSkin::$path), [
//                'sortListColumns' => $this->getSortListColumns($config, $instanceId),
//                'sortFormColumns' => $this->getSortFormColumns($config, $instanceId),
//                'config' => $config
//            ]);
//        } else {
//            return parent::renderSetting($config);
//        }

        return parent::renderSetting($config);
    }

    /**
     * get sort list columns
     *
     * @param array  $config     board config
     * @param string $instanceId board instance id
     * @return array
     * @deprecated since beta.24. use ConfigHandler::getSortListColumns() instead
     */
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

            if ($dynamicFieldConfig->get('use') === true &&
                in_array($dynamicFieldConfig->get('id'), $sortListColumns) === false) {
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

    /**
     * get sort form columns
     *
     * @param array  $config     board config
     * @param string $instanceId board instance id
     * @return array
     * @deprecated since beta.24. use ConfigHandler::getSortFormColumns() instead
     */
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

            if ($dynamicFieldConfig->get('use') === true &&
                in_array($dynamicFieldConfig->get('id'), $sortFormColumns) === false) {
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
        ) ? true : false;
    }
}
