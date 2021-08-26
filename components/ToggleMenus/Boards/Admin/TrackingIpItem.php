<?php
/**
 * TrackingIpItem
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
namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards\Admin;

use App\ToggleMenus\User\UserToggleMenu;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\User\Models\User;

/**
 * TrackingIpItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2021 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class TrackingIpItem extends UserToggleMenu
{
    /** @var string */
    protected static $id = 'module/board@board/toggleMenu/board@adminTrackingIpItem';

    /**
     * Tracking IP Item's title
     *
     * @return string
     */
    public static function getTitle(): string
    {
        return xe_trans('board::trackIP');
    }

    /**
     * Tracking IP Item's text
     *
     * @return string
     */
    public function getText(): string
    {
        return xe_trans('board::trackIP');
    }

    /**
     * Tracking IP Item's type
     *
     * @return string
     */
    public function getType(): string
    {
        return static::MENUTYPE_LINK;
    }

    /**
     * Tracking IP Item's action
     *
     * @return string
     */
    public function getAction(): string
    {
        $board = Board::findOrFail($this->identifier);

        return route('settings.board.board.docs.index', [
            'search_target' => 'ip',
            'search_keyword' => $board->getAttribute('ipaddress'),
        ]);
    }

    /**
     * Tracking IP Item's script
     *
     * @return string|null
     */
    public function getScript()
    {
        return null;
    }

    /**
     * Tracking IP Item's allows
     *
     * @return bool
     */
    public function allows(): bool
    {
        $user = auth()->user();
        return $user instanceof User ? $user->isAdmin() : false;
    }
}