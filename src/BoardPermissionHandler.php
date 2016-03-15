<?php
/**
 * BoardPermissionHandler
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

use Xpressengine\Permission\Permission;
use Xpressengine\Permission\PermissionHandler;
use Xpressengine\Permission\Grant;
use Xpressengine\Permission\Registered;
use Xpressengine\Permission\Action;
use Xpressengine\Plugin\PluginRegister;

/**
 * BoardPermissionHandler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class BoardPermissionHandler
{
    /** 문서 생성 퍼미션 action 이름 */
    const ACTION_CREATE = 'create';

    /** 문서 조회 퍼미션 action 이름 */
    const ACTION_READ = 'read';

    /** 문서 목록 퍼미션 action 이름 */
    const ACTION_LIST = 'list';

    /** 문서 관리 퍼미션 action 이름 */
    const ACTION_MANAGE = 'manage';

    /**
     * 퍼미션 인스턴스 prefix 이름
     *
     * @var string
     */
    protected $prefix = 'module/board@board';

    /**
     * @var string
     */
    protected $permissionType = 'instance';

    /**
     * @var array
     */
    protected $actions = [
        self::ACTION_CREATE,
        self::ACTION_READ,
        self::ACTION_LIST,
        self::ACTION_MANAGE,
    ];

    /**
     * @var PermissionHandler
     */
    protected $permissionHandler;

    /**
     * @var ConfigHandler
     */
    protected $configHandler;

    /**
     * create instance
     *
     * @param PermissionHandler $permissionHandler permission factory instance
     * @param ConfigHandler $configHandler config handler
     */
    public function __construct(
        PermissionHandler $permissionHandler,
        ConfigHandler $configHandler
    ) {
        $this->permissionHandler = $permissionHandler;
        $this->configHandler = $configHandler;
    }

    /**
     * set prefix
     *
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * 퍼미션 인스턴스 이름 반환
     *
     * @param string $instanceId instance identifier
     * @return string
     */
    public function name($instanceId)
    {
        return sprintf('%s.%s', $this->prefix, $instanceId);
    }

    public function getDefaultPerms()
    {
        $default = $this->getDefault();

        $perms = [];
        foreach ($this->actions as $actionName) {
            $mode = "top";
            $grant = $default->pure($actionName);

            $perms[] = [
                'mode' => $mode,
                'title' => $actionName,
                'grant' => $grant,
                'groups' => [],
            ];
        }

        return $perms;
    }

    public function getPerms($instanceId)
    {
        $default = $this->getDefault();
        $permission = $this->get($instanceId);

        $perms = [];
        foreach ($this->actions as $actionName) {
            $mode = "inherit";
            if ($permission !== null) {
                $pureGrant = $permission->pure($actionName);
                $mode = ($pureGrant === null) ? "inherit" : "manual";
            }

            $grant = $default->pure($actionName);
            if ($permission !== null && $permission->pure($actionName) !== null) {
                $grant = $permission->pure($actionName);
            }
            $perms[] = [
                'mode' => $mode,
                'title' => $actionName,
                'grant' => $grant,
                'groups' => [], // 그룹 정보는 어떻게 획득해야 하나
            ];
        }

        return $perms;
    }

    /**
     * 권한 객체 반환
     *
     * @param string $instanceId instance identifier
     * @return \Xpressengine\Permission\Permission
     */
    public function get($instanceId)
    {
        return $this->permissionHandler->get($this->name($instanceId));
    }

    /**
     * 권한 설정
     *
     * @param string $instanceId instance identifier
     * @param Grant  $grant      grant information object
     * @return void
     */
    public function set($instanceId, Grant $grant)
    {
        $this->permissionHandler->register($this->name($instanceId), $grant);
    }

    /**
     * 게시판 기본 권한 반환
     * install 시 설정 하기 위한 기본 권한
     *
     * @return Grant
     */
    public function getDefaultGrant()
    {
        $grant = new Grant();

        foreach ($this->actions as $action) {
            if ($action == self::ACTION_MANAGE) {
                $perm = [
                    Grant::RATING_TYPE => 'manager',
                    Grant::GROUP_TYPE => [],
                    Grant::USER_TYPE => [],
                    Grant::EXCEPT_TYPE => []
                ];
            } elseif ($action == self::ACTION_LIST || $action == self::ACTION_READ) {
                $perm = [
                    Grant::RATING_TYPE => 'guest',
                    Grant::GROUP_TYPE => [],
                    Grant::USER_TYPE => [],
                    Grant::EXCEPT_TYPE => []
                ];
            } else {
                $perm = [
                    Grant::RATING_TYPE => 'member',
                    Grant::GROUP_TYPE => [],
                    Grant::USER_TYPE => [],
                    Grant::EXCEPT_TYPE => []
                ];
            }

            $grant = $this->createGrant($grant, $action, $perm);
        }

        return $grant;
    }

    /**
     * 게시판 기본 권한 반환
     *
     * @return null|\Xpressengine\Permission\Permission
     */
    public function getDefault()
    {
        $permission = $this->permissionHandler->get($this->prefix);
        if ($permission === null) {
            $this->setDefault($this->getDefaultGrant());
            $permission = $this->permissionHandler->get($this->prefix);
        }

        return $permission;
    }

    /**
     * 게시판 기본 권한 설정
     *
     * @param Grant $grant grant information object
     * @return void
     */
    public function setDefault(Grant $grant)
    {
        $this->permissionHandler->register($this->prefix, $grant);
    }

    /**
     * grant 를 생성해서 반환
     *
     * @param Grant  $grant       grant instance
     * @param string $action      action name
     * @param array  $permissions permissions
     * @return Grant
     */
    public function createGrant(Grant $grant, $action, $permissions)
    {
        foreach ($permissions as $type => $value) {
            $grant->add($action, $type, $value);
        }

        return $grant;
    }

    /**
     * 게시판에서 사용하는 권한 action 리스트 반환
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }
}
