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
namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Users;

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
    protected static $id = 'user/toggleMenu/board@trackingWroteItem';

    /** @var string */
    private $boardInstanceId;

    /**
     * Tracking Wrote Item's title
     *
     * @return string
     */
    public static function getTitle(): string
    {
        return xe_trans('board::searchWrote');
    }

    /**
     * Tracking Wrote Item constructor.
     */
    public function __construct()
    {
        $this->setBoardInstanceId();
    }

    /**
     * Set Board Instance Id
     */
    protected function setBoardInstanceId()
    {
        $menuItem = null;

        $path = parse_url(url()->previous())['path'];
        $moduleUrl = array_get(explode('/', $path), 1);

        if (empty($moduleUrl)) {
            $menuItem = app('xe.menu')->items()->query()->where('type', 'board@board')->where('id', app('xe.site')->getHomeInstanceId())->first();
        } else {
            $menuItem = app('xe.menu')->items()->query()->where('type', 'board@board')->where('url', $moduleUrl)->first();
        }

        if ($menuItem !== null) {
            $this->boardInstanceId = $menuItem->getAttribute('id');
        }
    }

    /**
     * Tracking Wrote Item's Text
     *
     * @return string
     */
    public function getText(): string
    {
        return xe_trans('board::searchWrote');
    }

    /**
     * Tracking Wrote Item's Type
     *
     * @return string
     */
    public function getType(): string
    {
        return static::MENUTYPE_LINK;
    }

    /**
     * Tracking Wrote Item's Action
     *
     * @return string
     */
    public function getAction(): string
    {
        $user = User::findOrFail($this->identifier);
        return instance_route('index', ['writer' => $user->display_name], $this->boardInstanceId);
    }

    /**
     * Tracking Wrote Item's Allows
     *
     * @return bool
     */
    public function allows(): bool
    {
        return $this->boardInstanceId !== null;
    }

    /**
     * Tracking Wrote Item's Script
     *
     * @return string|null
     */
    public function getScript()
    {
        return null;
    }
}