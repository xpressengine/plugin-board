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

use Xpressengine\Permission\PermissionHandler;
use Xpressengine\Permission\Grant;
use Xpressengine\Permission\Registered;
use Xpressengine\Permission\Action;
use Xpressengine\Member\Repositories\GroupRepositoryInterface as Assignor;

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
    /** 문서 목록 퍼미션 action 이름 */
    const ACTION_LIST = 'list';

    /** 문서 관리 퍼미션 action 이름 */
    const ACTION_MANAGE = 'manage';

    /**
     * 퍼미션 인스턴스 prefix 이름
     *
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $permissionType = 'instance';

    /**
     * @var array
     */
    protected $actions = [
        Action::CREATE,
        Action::READ,
        self::ACTION_LIST,
        self::ACTION_MANAGE,
    ];

    /**
     * @var Permissions
     */
    protected $permissions;

    /**
     * 회원 패키지의 그룹 관리자
     *
     * @var Assignor
     */
    protected $assignor;

    /**
     * @var Action
     */
    protected $action;

    /**
     * @var ConfigHandler
     */
    protected $configHandler;

    /**
     * create instance
     *
     * @param PermissionHandler   $permissionHandler   permission factory instance
     * @param Assignor      $assignor      assignor
     * @param Action        $action        action
     * @param ConfigHandler $configHandler config handler
     */
    public function __construct(
        PermissionHandler $permissionHandler,
        Assignor $assignor,
        Action $action,
        ConfigHandler $configHandler
    ) {
        $this->permissionHandler = $permissionHandler;
        $this->assignor = $assignor;
        $this->action = $action;
        $this->configHandler = $configHandler;

        $this->action->add(self::ACTION_LIST);
        $this->action->add(self::ACTION_MANAGE);
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
     * 글 리스트 권한 체크
     *
     * @param string $instanceId instance id(board id)
     * @return bool
     */
    public function hasList($instanceId)
    {
        $permission = $this->get($instanceId);
        if ($permission->unables(self::ACTION_LIST) === true) {
            return false;
        }

        return true;
    }

    /**
     * 글 생성 권한 체크
     *
     * @param string $instanceId instance id(board id)
     * @return bool
     */
    public function hasCreate($instanceId)
    {
        $permission = $this->get($instanceId);
        if ($permission->unables(Action::CREATE) === true) {
            return false;
        }

        return true;
    }

    /**
     * 글 조회 권한 체크
     *
     * @param string $instanceId instance id(board id)
     * @return bool
     */
    public function hasRead($instanceId)
    {
        $permission = $this->get($instanceId);
        if ($permission->unables(Action::READ) === true) {
            return false;
        }

        return true;
    }

    /**
     * 퍼미션 인스턴스 이름 반환
     *
     * @param string $instanceId instance identifier
     * @return string
     */
    private function name($instanceId)
    {
        return sprintf('%s.%s', $this->prefix, $instanceId);
    }

    /**
     * 권한 객체 반환
     *
     * @param string $instanceId instance identifier
     * @return \Xpressengine\Permission\Permissions\InstancePermission
     */
    public function get($instanceId)
    {
        return $this->permissions->instance($this->name($instanceId));
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
        $this->permissions->register('instance', $this->name($instanceId), $grant);
    }

    /**
     * 게시판 기본 권한 반환
     * install 시 설정 하기 위한 기본 권한
     *
     * @return Grant
     */
    public function getDefault()
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
            } elseif ($action == self::ACTION_LIST || $action == Action::READ) {
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
     * 게시판 기본 권한 설정
     *
     * @param Grant $grant grant information object
     * @return void
     */
    public function setDefault(Grant $grant)
    {
        $this->permissions->register('instance', $this->prefix, $grant);
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
     * 퍼미션 grant 반환
     *
     * @param Registered $registered registered permission
     * @param string     $action     permission action name(Action::CREATE|self::ACTION_MANAGE)
     * @return array
     */
    public function getGrant(Registered $registered, $action)
    {
        if ($action == self::ACTION_MANAGE) {
            $grant = $this->getManageGrant($registered);
        } else {
            $defaultPerm = [
                Grant::RATING_TYPE => '',
                Grant::GROUP_TYPE => [],
                Grant::USER_TYPE => [],
                Grant::EXCEPT_TYPE => []
            ];

            if ($registered[$action] != null) {
                $grant = array_merge($defaultPerm, $registered[$action]);
            } else {
                $grant = $defaultPerm;
            }
        }

        return $grant;
    }

    /**
     * 관리 권한 설정을 위한 기본 퍼미션 반환
     * 관리 권한은 다른 권한 설정과 다른 점이 있다(RATING_TYPE 이 제한적으로 제공 된다거나..)
     *
     * @param Registered $registered registered permission
     * @return array
     */
    public function getManageGrant(Registered $registered)
    {
        $defaultPerm = [
            Grant::RATING_TYPE => '',
            Grant::GROUP_TYPE => [],
            Grant::USER_TYPE => [],
            Grant::EXCEPT_TYPE => []
        ];

        if ($registered[self::ACTION_MANAGE] != null) {
            $grant = array_merge($defaultPerm, $registered[self::ACTION_MANAGE]);
        } else {
            $grant = $defaultPerm;
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

    /**
     * action 의 permission 설정 반환
     *
     * @param string $instanceId instance identifier
     * @return array
     */
    public function getPerms($instanceId)
    {
        $registered = $this->get($instanceId)->getRegistered();

        $perms = [];
        foreach ($this->actions as $action) {
            $pureGrant = $registered->pure($action);
            $mode = ($pureGrant === null) ? "inherit" : "manual";

            $perms[] = [
                'mode' => $mode,
                'title' => $action,
                'grant' => $this->getGrant($registered, $action),
                'groups' => $this->assignor->all(),
            ];
        }

        return $perms;
    }

    /**
     * check manager
     *
     * @param bool   $guest      is guest
     * @param string $instanceId instance id
     * @return bool
     */
    public function isManager($guest, $instanceId)
    {
        return true;
        if ($guest === false && $this->get($instanceId)->ables(self::ACTION_MANAGE) === true)
        {
            return true;
        }
        return false;
    }
}
