<?php
/**
 * ConfigHandler
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
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class ConfigHandler extends AbstractConfigHandler
{
    /**
     * config package name
     * 다른 모듈과 충돌을 피하기 위해 설정 이름을 모듈 이름으로 선언
     */
    const CONFIG_NAME = 'module/board@board';

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
        'anonymity' => 'disuse',
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
        'newCommentNotice' => false,
        'secretPost' => true,
        'noticePost' => true,
        'replyPost' => false,
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

    public static function make(): ConfigHandler
    {
        return app(self::class);
    }

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
        parent::__construct($configManager);
        $this->dynamicField = $dynamicField;
        $this->document = $document;
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
        return count($configs) == 0 ? [] : $configs;
    }

    /**
     * get document config
     *
     * @param string $boardId board id
     * @return ConfigEntity
     */
    public function getDocument(string $boardId): ConfigEntity
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
