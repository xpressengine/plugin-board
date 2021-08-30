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

use Xpressengine\Plugins\Board\Checkers\CheckerItem;
use Xpressengine\Plugins\Board\Checkers\Logics\IsOwnerLogic;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Plugin as BoardPlugin;
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

    /** @var string */
    private static $allowsEvent = 'xe.plugin.board.toggleMenu.deleteItem.allows';

    /** @var CheckerItem */
    private $checkerItem;

    /**
     * get allows event
     *
     * @return string
     */
    public static function getAllowsEvent(): string
    {
        return static::$allowsEvent;
    }

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
     * Delete Item constructor.
     */
    public function __construct()
    {
        $this->checkerItem = new CheckerItem();
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
        if (app('xe.board.permission')->checkManageAction($this->instanceId) === true) {
            return true;
        }

        try {
            /** @var Board $board */
            $board = Board::findOrFail($this->identifier);

            \Event::fire(self::$allowsEvent, [$board, &$this->checkerItem]);
            $this->checkerItem = new IsOwnerLogic($this->checkerItem);

            return $this->checkerItem->operation($board, \Auth::user());
        }

        catch (\Exception $exception) {
            return false;
        }
    }
}
