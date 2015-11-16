<?php
/**
 * Board config handler
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
namespace Xpressengine\Plugins\Board;

use Xpressengine\Config\ConfigManager;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\DynamicField\ConfigHandler as DynamicFieldConfigHandler;
use Xpressengine\Document\ConfigHandler as DocumentConfigHandler;

/**
 * Board config handler
 * 게시판의 설정을 관리하기 위해 ConfigManager 사용
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
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
    protected $defaultListColumns = [
        'title', 'writer', 'createdAt', 'assentCount', 'dissentCount', 'readCount', 'updatedAt',
    ];

    /**
     * @var array
     */
    protected $defaultFormColumns = [
        'title', 'content',
    ];

    /**
     * 기본으로 사용 될 정렬 정렬 컴퍼넌트
     *
     * @var string
     */
    protected $defaultOrder = 'module/board@board/order/board@recentlyCreated';

    /**
     * @var array
     */
    protected $defaultConfig = [
        'boardId' => null,
        'boardName' => null,
        'skinId' => '',
        'perPage' => 10,
        //'searchPerPage' => 10,
        'pageCount' => 10,
        'newTime' => 86400, // 새글로 처리하는 시간
        'comment' => true,
        'assent' => true,
        'dissent' => false,
        'anonymity' => false,
        'anonymityName' => 'Anonymity',
        //'shortId' => true,
        'division' => false,
        'revision' => true,
        'order' => 'module/board@board/order/board@recentlyCreated',
        'listColumns' => null,
        'formColumns' => null,
        'dynamicFieldList' => [],
        'recursiveDelete' => true,
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
     * get list columns information
     *
     * @param string $boardId board id
     * @return array
     */
    public function listColumns($boardId)
    {
        $columns = $this->getDefaultListColumns();
        $configs = $this->getDynamicFields($this->get($boardId));
        /**
         * @var ConfigEntity $config
         */
        foreach ($configs as $config) {
            if ($config->get('sortable') == true || $config->get('searchable') == true) {
                $columns[] = $config->get('id');
            }
        }
        return $columns;
    }

    /**
     * get form column names
     *
     * @param string $boardId board id
     * @return array
     */
    public function formColumns($boardId)
    {
        $config = $this->get($boardId);
        $columns = $config->get('formColumns');

        $configs = $this->getDynamicFields($config);

        $dynamicFieldIds = [];
        /** @var ConfigEntity $config */
        foreach ($configs as $config) {
            $dynamicFieldIds[] = $config->get('id');
        }

        // 없는 dynamic field 제거
        foreach (array_diff($columns, $this->getDefaultFormColumns()) as $columnName) {
            if (in_array($columnName, $dynamicFieldIds) === false) {
                $key = array_search($columnName, $columns);
                if ($key !== false) {
                    unset($columns[$key]);
                }
            }
        }

        // 설정 안된.. 새로 생성된 df 넣어줌
        /**
         * @var ConfigEntity $config
         */
        foreach ($configs as $config) {
            if (in_array($config->get('id'), $columns) === false) {
                $columns[] = $config->get('id');
            }
        }

        return $columns;
    }

    /**
     * 비회원 글 생성 설정 체크
     *
     * @param ConfigEntity $config board config entity
     * @return bool
     * @deprecated 권한에서 처리하면 되는 내용
     */
    public function isGuestSupport(ConfigEntity $config)
    {
        if ($config->get('guest') !== true) {
            return false;
        }
        return true;
    }

    /**
     * 기본 게시판 설정 반환. 설정이 없을 경우 등록 후 반환
     *
     * @return ConfigEntity
     */
    public function getDefault()
    {
        $parent = $this->configManager->get(self::CONFIG_NAME);

        if ($parent == null) {
            $default = $this->defaultConfig;

            $default['order'] = $this->defaultOrder;
            $default['listColumns'] = $this->getDefaultListColumns();
            $default['formColumns'] = $this->getDefaultFormColumns();

            $parent = $this->configManager->add(self::CONFIG_NAME, $default);
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
        return $this->configManager->add(self::CONFIG_NAME, $args);
    }

    /**
     * 기본 게시판 설정 수정
     *
     * @param array $args config arguments
     * @return ConfigEntity
     */
    public function putDefault(array $args)
    {
        return $this->configManager->put(self::CONFIG_NAME, $args);
    }

    /**
     * get default list columns
     *
     * @return array
     */
    public function getDefaultListColumns()
    {
        return $this->defaultListColumns;
    }

    /**
     * get default form columns
     *
     * @return array
     */
    public function getDefaultFormColumns()
    {
        return $this->defaultFormColumns;
    }

    /**
     * 게시판 인스턴스 설정 이름 반환
     *
     * @param string $boardId board id
     * @return string
     */
    private function name($boardId)
    {
        return sprintf('%s.%s', self::CONFIG_NAME, $boardId);
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
     * add config
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
        $parent = $this->configManager->get(self::CONFIG_NAME);
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
}
