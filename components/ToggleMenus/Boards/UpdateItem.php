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

use Xpressengine\Plugins\Board\Checkers\CheckerItem;
use Xpressengine\Plugins\Board\Checkers\Logics\IsOwnerLogic;
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

    /** @var string */
    private static $allowsEvent = 'xe.plugin.board.toggleMenu.updateItem.allows';

    /** @var CheckerItem */
    private $checkerItem;

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
     * get allows event
     *
     * @return string
     */
    public static function getAllowsEvent(): string
    {
        return static::$allowsEvent;
    }


    /**
     * Update Item constructor.
     */
    public function __construct()
    {
        $this->checkerItem = new CheckerItem();
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
