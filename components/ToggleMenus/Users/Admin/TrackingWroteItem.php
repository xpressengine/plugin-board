<?php
/**
 * TrackingWroteItem
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
namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Users\Admin;

use App\ToggleMenus\User\UserToggleMenu;
use Xpressengine\User\Models\User;

/**
 * TrackingWroteItem
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
class TrackingWroteItem extends UserToggleMenu
{
    /** @var string */
    protected static $id = 'user/toggleMenu/board@adminTrackingWroteItem';

    /**
     * Title Of Tracking Wrote Item for admin
     *
     * @return string
     */
    public static function getTitle(): string
    {
        return xe_trans('board::trackWrote');
    }

    /**
     * Text Of Tracking Wrote Item for admin
     *
     * @return string
     */
    public function getText(): string
    {
        return xe_trans('board::trackWrote');
    }

    /**
     * Type Of Tracking Wrote Item for admin
     *
     * @return string
     */
    public function getType(): string
    {
        return static::MENUTYPE_LINK;
    }

    /**
     * Action Of Tracking Wrote Item for admin
     *
     * @return string
     */
    public function getAction(): string
    {
        $user = User::findOrFail($this->identifier);

        return route('settings.board.board.docs.index', [
            'search_target' => 'writerId',
            'search_keyword' => $user->getAttribute('login_id'),
        ]);
    }

    /**
     *  Script Of Tracking Wrote Item for admin
     *
     * @return string|null
     */
    public function getScript()
    {
        return null;
    }

    /**
     * Allows Of Tracking Wrote Item for admin
     *
     * @return bool
     */
    public function allows(): bool
    {
        $user = auth()->user();
        return $user instanceof User ? $user->isAdmin() : false;
    }
}