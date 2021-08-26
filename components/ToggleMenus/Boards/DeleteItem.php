<?php
/**
 * DeleteItem
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2021 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Plugin as BoardPlugin;
use Xpressengine\Plugins\Board\UrlHandler as BoardUrlHandler;
use Xpressengine\ToggleMenu\AbstractToggleMenu;

/**
 * DeleteItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2021 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class DeleteItem extends AbstractToggleMenu
{
    /** @var string */
    protected static $id = 'module/board@board/toggleMenu/board@deleteItem';

    /**
     * Delete Toggle Item's title
     *
     * @return string
     */
    public static function getTitle(): string
    {
        return xe_trans('xe::delete');
    }

    /**
     * Delete Toggle Item's text
     *
     * @return string
     */
    public function getText(): string
    {
        return xe_trans('xe::delete');
    }

    /**
     * Delete Toggle Item's Type
     *
     * @return string
     */
    public function getType(): string
    {
        return static::MENUTYPE_EXEC;
    }

    /**
     * Delete Toggle Item's Action
     *
     * @return string
     */
    public function getAction(): string
    {
        $params = [
            'id' => $this->identifier
        ];

        return sprintf('BoardToggleMenu.delete(event, "%s")', instance_route('destroy', $params, $this->instanceId));
    }

    /**
     * Delete Toggle Item's Script
     *
     * @return string
     */
    public function getScript(): string
    {
        \XeFrontend::translation(['board::msgDeleteConfirm']);
        return BoardPlugin::asset('assets/js/src/toggleMenu.js');
    }

    /**
     * Delete Toggle Item's Allows
     *
     * @return bool
     */
    public function allows(): bool
    {
        /** @var Board $board */
        $board = Board::findOrFail($this->identifier);

        if (app('xe.board.permission')->checkManageAction($this->instanceId)) {
            return true;
        }

        return $board->user_id === auth()->id();
    }
}
