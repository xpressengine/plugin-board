<?php
/**
 * TrashItem
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
namespace Xpressengine\Plugins\BoardComponents\ToggleMenus\Boards;

use Gate;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\ToggleMenu\AbstractToggleMenu;
use Xpressengine\Permission\Instance;

/**
 * TrashItem
 *
 * Toggle menu item
 * 팝업 메뉴에 휴지통으로 이동 처리
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class TrashItem extends AbstractToggleMenu
{
    /**
     * check permission
     *
     * @return bool
     */
    public function allows()
    {
        $doc = Board::find($this->identifier);
        $configHandler = app('xe.board.config');
        $boardPermission = app('xe.board.permission');

        $config = $configHandler->get($doc->instanceId);
        $isManger = false;
        if ($config !== null) {

            if (Gate::allows(
                BoardPermissionHandler::ACTION_MANAGE,
                new Instance($boardPermission->name($doc->instanceId))
            )) {
                $isManger = true;
            };
        }

        return $isManger;
    }

    /**
     * get text
     *
     * @return string
     */
    public function getText()
    {
        return xe_trans('xe::moveToTrash');
    }

    /**
     * get type
     *
     * @return string
     */
    public function getType()
    {
        return static::MENUTYPE_EXEC;
    }

    /**
     * get action url
     *
     * @return string
     */
    public function getAction()
    {
        $doc = Board::find($this->identifier);

        $url = app('xe.board.url')->get('trash', ['id' => $this->identifier], $doc->instanceId);

        return 'var url = "' . $url . '" + window.location.search;
            XE.ajax(url, {
                type: "post",
                dataType: "json",
                data: {
                    id: "' . $this->identifier . '"
                },
                success: function (data) {
                    location.replace(data.links.href);
                }
            });';
    }

    /**
     * get script
     *
     * @return null
     */
    public function getScript()
    {
        return null;
    }
}
