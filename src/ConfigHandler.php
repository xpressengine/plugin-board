<?php
/**
 * ConfigHandler
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Board;

use Xpressengine\Config\ConfigManager;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\DynamicField\ConfigHandler as DynamicFieldConfigHandler;
use Xpressengine\Document\ConfigHandler as DocumentConfigHandler;

/**
 * ConfigHandler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class ConfigHandler
{

    /**
     * config package name
     * 다른 모듈과 충돌을 피하기 위해 설정 이름을 모듈 이름으로 선언
     */
    const CONFIG_NAME = 'module/board@board';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var DynamicFieldConfigHandler
     */
    protected $dynamicField;

    /**
     * @var DocumentConfigHandler
     */
    protected $document;

    /**
     * @var array
     */
    protected $defaultConfig = [
        'boardId' => null,
        'boardName' => null,
        'skinId' => '',
        'perPage' => 10,
        'pageCount' => 10,
        'newTime' => 1, // 새글로 처리하는 시간
        'comment' => true,
        'assent' => true,
        'dissent' => false,
        'category' => false,
        'anonymity' => false,
        'anonymityName' => 'Anonymity',
        'managerEmail' => '',
        'division' => false,
        'revision' => true,
        'dynamicFieldList' => [],
        'recursiveDelete' => true,
        'orderType' => '',
        'useCaptcha' => false,
        'useTag' => true,
        'urlType' => 'slug',
        'deleteToTrash' => false,
        'newCommentNotice' => true,
        'secretPost' => true,
        'useApprove' => false,
    ];

    /**
     * @var array
     */
    const DEFAULT_LIST_COLUMNS = [
        'favorite', 'title', 'writer', 'assent_count', 'read_count', 'created_at', 'updated_at', 'dissent_count',
    ];

    const DEFAULT_SELECTED_LIST_COLUMNS = [
        'favorite', 'title', 'writer',  'assent_count', 'read_count', 'created_at',
    ];

    /**
     * @var array
     */
    const DEFAULT_FORM_COLUMNS = [
        'title', 'content',
    ];

    /**
     * @var array
     */
    const DEFAULT_SELECTED_FORM_COLUMNS = [
        'title', 'content',
    ];

    /**
     * create instance
     *
     * @param ConfigManager             $configManager config manager
     * @param DynamicFieldConfigHandler $dynamicField  dynamic field config handler
     * @param DocumentConfigHandler     $document      document config handler
     */
    public function __construct(
        ConfigManager $configManager,
        DynamicFieldConfigHandler $dynamicField,
        DocumentConfigHandler $document
    ) {
        $this->configManager = $configManager;
        $this->dynamicField = $dynamicField;
        $this->document = $document;
    }

    /**
     * 기본 게시판 설정 반환. 설정이 없을 경우 등록 후 반환
     *
     * @return ConfigEntity
     */
    public function getDefault()
    {
        $parent = $this->configManager->get(static::CONFIG_NAME);

        if ($parent == null) {
            $default = $this->defaultConfig;
            $parent = $this->configManager->add(static::CONFIG_NAME, $default);
        }

        return $parent;
    }

    /**
     * 기본 게시판 설정 등록
     *
     * @param array $args config arguments
     * @return ConfigEntity
     */
    public function addDefault(array $args)
    {
        return $this->configManager->add(static::CONFIG_NAME, $args);
    }

    /**
     * 기본 게시판 설정 수정
     *
     * @param array $args config arguments
     * @return ConfigEntity
     */
    public function putDefault(array $args)
    {
        return $this->configManager->put(static::CONFIG_NAME, $args);
    }

    /**
     * 게시판 인스턴스 설정 이름 반환
     *
     * @param string $boardId board id
     * @return string
     */
    private function name($boardId)
    {
        return sprintf('%s.%s', static::CONFIG_NAME, $boardId);
    }

    /**
     * add config
     *
     * @param array $params parameters
     * @return ConfigEntity
     * @throws \Xpressengine\Config\Exceptions\InvalidArgumentException
     */
    public function add(array $params)
    {
        return $this->configManager->add($this->name($params['boardId']), $params);
    }

    /**
     * put config
     *
     * @param array $params parameters
     * @return ConfigEntity
     * @throws \Xpressengine\Config\Exceptions\InvalidArgumentException
     */
    public function put(array $params)
    {
        return $this->configManager->put($this->name($params['boardId']), $params);
    }

    /**
     * modify config
     *
     * @param ConfigEntity $config
     * @return ConfigEntity
     */
    public function modify(ConfigEntity $config)
    {
        return $this->configManager->modify($config);
    }

    /**
     * remove config
     *
     * @param ConfigEntity $config board config entity
     * @return void
     */
    public function remove(ConfigEntity $config)
    {
        $this->configManager->remove($config);
    }

    /**
     * get all board configs
     *
     * @return array
     */
    public function gets()
    {
        $parent = $this->configManager->get(static::CONFIG_NAME);
        if ($parent === null) {
            return [];
        }

        $configs = $this->configManager->children($parent);
        return $configs;
    }

    /**
     * get board config
     *
     * @param string $boardId board id
     * @return ConfigEntity
     */
    public function get($boardId)
    {
        $config = $this->configManager->get($this->name($boardId));
        return $config;
    }

    /**
     * get dynamic field config list
     *
     * @param ConfigEntity $config board config entity
     * @return array
     */
    public function getDynamicFields(ConfigEntity $config)
    {
        $configs = $this->dynamicField->gets($config->get('documentGroup'));
        if (count($configs) == 0) {
            return [];
        }
        return $configs;
    }

    /**
     * get document config
     *
     * @param string $boardId board id
     * @return ConfigEntity
     */
    public function getDocument($boardId)
    {
        return $this->document->get($boardId);
    }

    /**
     * get sort list columns
     *
     * @param ConfigEntity $config board config
     * @return array
     */
    public function getSortListColumns(ConfigEntity $config)
    {
        if (empty($config->get('sortListColumns'))) {
            $sortListColumns = self::DEFAULT_LIST_COLUMNS;
        } else {
            $sortListColumns = $config->get('sortListColumns');
        }

        $dynamicFields = $this->getDynamicFields($config);
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

        $usableColumns = array_merge(self::DEFAULT_LIST_COLUMNS, $currentDynamicFields);
        foreach ($sortListColumns as $index => $column) {
            if (in_array($column, $usableColumns) === false) {
                unset($sortListColumns[$index]);
            }
        }

        // for beta.24 updated users
        if (in_array('favorite', $sortListColumns) == false) {
            array_unshift($sortListColumns, 'favorite');
        }

        return $sortListColumns;
    }

    /**
     * get sort form columns
     *
     * @param ConfigEntity $config board config
     * @return array
     */
    public function getSortFormColumns(ConfigEntity $config)
    {
        if (empty($config->get('sortFormColumns'))) {
            $sortFormColumns = self::DEFAULT_FORM_COLUMNS;
        } else {
            $sortFormColumns = $config->get('sortFormColumns');
        }
        $dynamicFields = $this->getDynamicFields($config);
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

        $usableColumns = array_merge(self::DEFAULT_FORM_COLUMNS, $currentDynamicFields);
        foreach ($sortFormColumns as $index => $column) {
            if (in_array($column, $usableColumns) === false) {
                unset($sortFormColumns[$index]);
            }
        }

        return $sortFormColumns;
    }
}
