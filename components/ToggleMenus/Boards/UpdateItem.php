<?php
/**
 * UpdateItem
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
use Xpressengine\ToggleMenu\AbstractToggleMenu;

/**
 * UpdateItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2021 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class UpdateItem extends AbstractToggleMenu
{
    /** @var string */
    protected static $id = 'module/board@board/toggleMenu/board@updateItem';

    /**
     * Updated Toggle Item's title
     *
     * @return string
     */
    public static function getTitle(): string
    {
        return xe_trans('xe::update');
    }

    /**
     * Updated Toggle Item's Text
     *
     * @return string
     */
    public function getText(): string
    {
        return xe_trans('xe::update');
    }

    /**
     * Updated Toggle Item's type
     *
     * @return string
     */
    public function getType(): string
    {
        return static::MENUTYPE_LINK;
    }

    /**
     * Updated Toggle Item's action
     *
     * @return string
     */
    public function getAction(): string
    {
        $params = array_merge(request()->all(), ['id' => $this->identifier]);
        return instance_route('edit', $params, $this->instanceId);
    }

    /**
     * Updated Toggle Item's script
     *
     * @return null
     */
    public function getScript()
    {
        return null;
    }

    /**
     * Updated Toggle Item's allows
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
