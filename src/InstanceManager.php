<?php
/**
 * InstanceManager
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

use Xpressengine\Document\DocumentHandler;
use Xpressengine\DynamicField\DynamicFieldHandler;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Comment\Handler as CommentHandler;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Permission\Grant;
use Xpressengine\Plugins\Board\Exceptions\AlreadyExistsInstanceException;
use Xpressengine\Plugins\Board\Exceptions\InvalidConfigException;
use Xpressengine\Plugins\Board\Exceptions\RequiredBoardIdException;
use Xpressengine\Database\VirtualConnectionInterface as VirtualConnection;

/**
 * InstanceManager
 *
 * 메뉴에서 게시판 추가할 때 추가된 게시판 관리
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
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
    protected $commentHandler;

    /**
     * @var ConfigHandler
     */
    protected $configHandler;

    /**
     * @var BoardPermissionHandler
     */
    protected $permissionHandler;

    /**
     * create instance
     *
     * @param VirtualConnection      $conn              database connection
     * @param DocumentHandler        $document          document handler
     * @param DynamicFieldHandler    $dynamicField      dynamic field handler
     * @param ConfigHandler          $configHandler     config handler
     * @param BoardPermissionHandler $permissionHandler permission handler
     * @param CommentHandler         $commentHandler    comment handler
     */
    public function __construct(
        VirtualConnection $conn,
        DocumentHandler $document,
        DynamicFieldHandler $dynamicField,
        ConfigHandler $configHandler,
        BoardPermissionHandler $permissionHandler,
        CommentHandler $commentHandler
    ) {
        $this->conn = $conn;
        $this->document = $document;
        $this->dynamicField = $dynamicField;
        $this->configHandler = $configHandler;
        $this->permissionHandler = $permissionHandler;
        $this->commentHandler = $commentHandler;
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
            throw new RequiredBoardIdException;
        }

        $config = $this->configHandler->get($params['boardId']);
        if ($config !== null) {
            throw new AlreadyExistsInstanceException;
        }

        $this->conn->beginTransaction();

        $documentConfig = $this->document->createInstance($params['boardId'], $params);

        $this->createCommentConfig($documentConfig);

        $params['documentGroup'] = $documentConfig->get('group');
        $params['commentGroup'] = 'comments_' . $documentConfig->get('instanceId');

        $config = $this->configHandler->add($params);

        $this->permissionHandler->setByInstanceId($params['boardId'], new Grant());

        $this->conn->commit();

        return $config;
    }

    /**
     * create comment config(create new comment instance)
     *
     * @param ConfigEntity $config document config entity
     * @return void
     */
    protected function createCommentConfig(ConfigEntity $config)
    {
        $this->commentHandler->createInstance($config->get('instanceId'), $config->get('division'));
        $this->commentHandler->configure(
            $this->commentHandler->getInstanceId($config->get('instanceId')),
            ['useWysiwyg' => true]
        );
    }

    /**
     * 게시판 설정 변경
     *
     * @param array $params parameters
     * @param array $unsetKeys unset keys
     * @return ConfigEntity
     */
    public function updateConfig(array $params, array $unsetKeys = [])
    {
        if (empty($params['boardId']) === true) {
            throw new RequiredBoardIdException;
        }

        $config = $this->configHandler->get($params['boardId']);
        if ($config === null) {
            throw new InvalidConfigException;
        }

        $configHandler = $this->document->getConfigHandler();
        $documentConfig = $configHandler->get($params['boardId']);

        foreach ($params as $key => $value) {
            $documentConfig->set($key, $value);
            $config->set($key, $value);
        }

        foreach ($unsetKeys as $unsetKey) {
            if ($documentConfig->offsetExists($unsetKey)) {
                $documentConfig->set($unsetKey, null);
            }

            if ($config->offsetExists($unsetKey)) {
                $config->set($unsetKey, null);
            }
        }

        $this->conn->beginTransaction();

        $config->set('documentGroup', $documentConfig->get('group'));
        $config->set('commentGroup', 'comments_' . $documentConfig->get('instanceId'));
        $config = $this->configHandler->modify($config);

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

        $commentInstanceId = $this->commentHandler->getInstanceId($boardId);

        if ($commentInstanceId !== null) {
            $this->commentHandler->drop($commentInstanceId);
        }

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
     * @param string $instanceId instance id
     * @return string
     */
    public function summary($instanceId)
    {
        /** @var Board $model */
        $model = Board::division($instanceId);
        $documentCount = $model->where('instance_id', $instanceId)->count();
        $configs = $this->configHandler->getDynamicFields($this->configHandler->get($instanceId));
        $dynamicFieldCount = count($configs);

        return [
            'documentCount' => $documentCount,
            'dynamicFieldCount' => $dynamicFieldCount,
        ];
    }
}
