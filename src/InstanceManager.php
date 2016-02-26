<?php
/**
 * InstanceManager
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
namespace Xpressengine\Plugins\Board;

use Category;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\DynamicField\DynamicFieldHandler;
use Xpressengine\Comment\CommentHandler;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugins\Board\Exceptions\AlreadyExistsInstanceException;
use Xpressengine\Plugins\Board\Exceptions\InvalidConfigException;
use Xpressengine\Plugins\Board\Exceptions\RequiredValueException;
use Xpressengine\Database\VirtualConnectionInterface as VirtualConnection;

/**
 * InstanceManager
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class InstanceManager
{
    /**
     * @var \Xpressengine\Database\VirtualConnectionInterface
     */
    protected $conn;

    /**
     * @var DocumentHandler
     */
    protected $document;

    /**
     * @var DynamicFieldHandler
     */
    protected $dynamicField;

    /**
     * @var CommentHandler
     */
    protected $comment;

    /**
     * @var ConfigHandler
     */
    protected $configHandler;

    /**
     * create instance
     *
     * @param VirtualConnection   $conn          database connection
     * @param DocumentHandler     $document      document handler
     * @param DynamicFieldHandler $dynamicField  dynamic field handler
     * @param CommentHandler      $comment       comment handler
     * @param ConfigHandler       $configHandler config handler
     */
    public function __construct(
        VirtualConnection $conn,
        DocumentHandler $document,
        DynamicFieldHandler $dynamicField,
        CommentHandler $comment,
        ConfigHandler $configHandler
    ) {
        $this->conn = $conn;
        $this->document = $document;
        $this->dynamicField = $dynamicField;
        $this->comment = $comment;
        $this->configHandler = $configHandler;
    }

    /**
     * 게시판 생성
     *
     * @param array $params parameters
     * @return ConfigEntity
     */
    public function create(array $params)
    {
        if (empty($params['boardId']) === true) {
            throw new RequiredValueException;
        }

        $config = $this->configHandler->get($params['boardId']);
        if ($config !== null) {
            throw new AlreadyExistsInstanceException;
        }

        $this->conn->beginTransaction();

        $documentConfig = $this->document->createInstance($params['boardId'], $params);

        // create comment config(create new comment instance)
        $this->comment->createInstance($documentConfig->get('instanceId'), $documentConfig->get('division'));
        $this->comment->configure($documentConfig->get('instanceId'), ['useWysiwyg' => true]);

        $params['documentGroup'] = $documentConfig->get('group');
        $params['commentGroup'] = 'comments_' . $documentConfig->get('instanceId');

        $config = $this->configHandler->add($params);

        // category dynamic field create
        //$this->createDefaultDynamicField($config);

        $this->conn->commit();

        return $config;
    }

    /**
     * create default dynamic field
     *
     * @param ConfigEntity $boardConfig board config entity
     * @deprecated
     */
    protected function createDefaultDynamicField(ConfigEntity $boardConfig)
    {
        $category = Category::create(['name' => 'board-default']);

        $config = new ConfigEntity;
        foreach ([
                     'group' => $boardConfig->get('documentGroup'),
                     'revision' => $boardConfig->get('revision'),
                     'id' => 'category',
                     'typeId' => 'FieldType/xpressengine@Category',
                     'label' => 'board::category',
                     'skinId' => 'FieldType/xpressengine@Category/FieldSkin/xpressengine@default',
                     'use' => true,
                     'searchable' => true,
                     'required' => true,
                     'sortable' => false,
                     'tableMethod' => false,
                     'categoryId' => $category->id,
                     'colorSet' => 'default',
                     'joinColumnName' => 'id'
                 ] as $key => $value) {
            $config->set($key, $value);
        }

        $this->dynamicField->create($config);
    }

    /**
     * 게시판 설정 변경
     *
     * @param array $params parameters
     * @return ConfigEntity
     */
    public function updateConfig(array $params)
    {
        if (empty($params['boardId']) === true) {
            throw new RequiredValueException;
        }

        $config = $this->configHandler->get($params['boardId']);
        if ($config === null) {
            throw new InvalidConfigException;
        }

        $params = array_merge($config->getPureAll(), $params);

        $this->conn->beginTransaction();

        $config = $this->configHandler->put($params);
        $configHandler = $this->document->getConfigHandler();
        $documentConfig = $configHandler->make($params['boardId'], $params);
        $this->document->getInstanceManager()->put($documentConfig);

        $this->conn->commit();

        return $config;
    }

    /**
     * 게시판 제거
     *
     * @param string $boardId board id
     * @return void
     */
    public function destroy($boardId)
    {
        $config = $this->configHandler->get($boardId);
        if ($config === null) {
            throw new Exceptions\InvalidConfigException;
        }

        $this->conn->beginTransaction();

        // get document config
        $this->document->destroyInstance($boardId);
        $this->comment->drop($boardId);

        // remove board config
        $this->configHandler->remove($config);

        // 연결된 df 제거
        foreach ($this->configHandler->getDynamicFields($config) as $config) {
            $this->dynamicField->drop($config);
        }

        $this->conn->commit();
    }

    /**
     * 게시판 요약 정보 반환
     *
     * @param string  $instanceId instance id
     * @param Handler $handler    board handler
     * @return string
     */
    public function summary($instanceId, Handler $handler)
    {
        $documentCount = $handler->countByBoardId($instanceId);
        $configs = $this->configHandler->getDynamicFields($this->configHandler->get($instanceId));
        $dynamicFieldCount = count($configs);

        return [
            'documentCount' => $documentCount,
            'dynamicFieldCount' => $dynamicFieldCount,
        ];

    }
}
