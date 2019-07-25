<?php
/**
 * BoardPermissionHandler
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright    2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Board;

use Xpressengine\Http\Request;
use Xpressengine\Permission\PermissionHandler;
use Xpressengine\Permission\Grant;
use Xpressengine\Permission\PermissionSupport;
use Xpressengine\Permission\Registered;
use Xpressengine\Permission\Action;
use Xpressengine\User\Rating;

/**
 * BoardPermissionHandler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class BoardPermissionHandler
{
    use PermissionSupport;

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
     * create instance
     *
     * @param PermissionHandler $permissionHandler permission factory instance
     */
    public function __construct(PermissionHandler $permissionHandler)
    {
        $this->permissionHandler = $permissionHandler;
    }

    /**
     * set prefix
     *
     * @param string $prefix config prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * get config prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
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

    /**
     * get global permissions
     *
     * @return array
     */
    public function getGlobalPerms()
    {
        return $this->getPermArguments($this->prefix, $this->getActions());
    }

    /**
     * get instance permissions
     *
     * @param string $instanceId board instance id
     * @return array
     */
    public function getPerms($instanceId)
    {
        return $this->getPermArguments($this->name($instanceId), $this->getActions());
    }

    /**
     * 권한 객체 반환
     *
     * @param string $instanceId board instance id
     * @return \Xpressengine\Permission\Permission
     */
    public function get($instanceId)
    {
        return $this->permissionHandler->get($this->name($instanceId));
    }

    /**
     * 권한 설정
     *
     * @param Request $request    request
     * @param string  $instanceId board instance id
     * @return void
     */
    public function set(Request $request, $instanceId)
    {
        $this->permissionRegister($request, $this->name($instanceId), $this->getActions());
    }

    /**
     * 인스턴스 아이디로 권한 설정
     *
     * @param string $instanceId board instance id
     * @param Grant  $grant      grant
     * @return void
     */
    public function setByInstanceId($instanceId, Grant $grant)
    {
        $this->permissionHandler->register($this->name($instanceId), $grant);
    }

    /**
     * 게시판 기본 권한 반환
     * install 시 설정 하기 위한 기본 권한
     *
     * @return Grant
     */
    public function addGlobal()
    {
        $grant = new Grant();

        foreach ($this->actions as $action) {
            if ($action == self::ACTION_MANAGE) {
                $perm = [
                    Grant::RATING_TYPE => Rating::MANAGER,
                    Grant::GROUP_TYPE => [],
                    Grant::USER_TYPE => [],
                    Grant::EXCEPT_TYPE => []
                ];
            } elseif ($action == self::ACTION_LIST || $action == self::ACTION_READ) {
                $perm = [
                    Grant::RATING_TYPE => Rating::GUEST,
                    Grant::GROUP_TYPE => [],
                    Grant::USER_TYPE => [],
                    Grant::EXCEPT_TYPE => []
                ];
            } else {
                $perm = [
                    Grant::RATING_TYPE => Rating::USER,
                    Grant::GROUP_TYPE => [],
                    Grant::USER_TYPE => [],
                    Grant::EXCEPT_TYPE => []
                ];
            }

            $grant = $this->addGrant($grant, $action, $perm);
        }

        $this->permissionHandler->register($this->getPrefix(), $grant);

        return $grant;
    }

    /**
     * 게시판 기본 권한 반환
     *
     * @return \Xpressengine\Permission\Permission
     */
    public function getGlobal()
    {
        $permission = $this->permissionHandler->get($this->prefix);
        return $permission;
    }

    /**
     * 게시판 기본 권한 설정
     *
     * @param Request $request request
     * @return void
     */
    public function setGlobal(Request $request)
    {
        $this->permissionRegister($request, $this->prefix, $this->getActions());
    }

    /**
     * grant 를 생성해서 반환
     *
     * @param Grant  $grant       grant instance
     * @param string $action      action name
     * @param array  $permissions permissions
     * @return Grant
     */
    public function addGrant(Grant $grant, $action, $permissions)
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
