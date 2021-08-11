<?php
/**
 * TrashItem
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
namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\ToggleMenu\AbstractToggleMenu;

/**
 * TrashItem
 *
 * Toggle menu item
 * 팝업 메뉴에 휴지통으로 이동 처리
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class TrashItem extends AbstractToggleMenu
{
    public static function getTitle(): string
    {
        return xe_trans('xe::trash');
    }

    public function __construct()
    {
        \XeFrontend::translation(['board::msgTrashConfirm']);
    }

    public function allows(): bool
    {
        $configHandler = app('xe.board.config');
        $boardPermission = app('xe.board.permission');

        $config = $configHandler->get($this->instanceId);
        return $config !== null ? $boardPermission->checkManageAction($this->instanceId) : false;
    }

    public function getText(): string
    {
        return xe_trans('xe::moveToTrash');
    }

    public function getType(): string
    {
        return static::MENUTYPE_EXEC;
    }

    public function getAction(): string
    {
        $url = app('xe.board.url')->get('trash', ['id' => $this->identifier], $this->instanceId);
        return sprintf('BoardToggleMenu.trash(event, "%s", "%s")', $url, $this->identifier);
    }

    /**
     * @return null
     */
    public function getScript()
    {
        return null;
    }
}
