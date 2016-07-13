<?php
/**
 * TrashItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\ToggleMenus;

use Gate;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\ToggleMenu\AbstractToggleMenu;
use Xpressengine\Permission\Instance;

/**
 * TrashItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Crop. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */
class TrashItem extends AbstractToggleMenu
{
    public function allows() {
        $doc = Board::find($this->identifier);
        $configHandler = app('xe.board.config');
        $boardPermission = app('xe.board.permission');

        $config = $configHandler->get($doc->instanceId);
        $isManger = false;
        if ($config !== null) {

            if (Gate::allows(
                BoardPermissionHandler::ACTION_MANAGE,
                new Instance($boardPermission->name($doc->instanceId)))
            ) {
                $isManger = true;
            };
        }

        return $isManger;
    }

    public function getText()
    {
        return xe_trans('xe::moveToTrash');
    }

    public function getType()
    {
        return static::MENUTYPE_LINK;
    }

    public function getAction()
    {
        $doc = Board::find($this->identifier);

        $config = app('xe.board.config')->get($doc->instanceId);

        return app('xe.board.url')->get('trash', ['id' => $this->identifier], $config);
    }

    public function getScript()
    {
    }
}
