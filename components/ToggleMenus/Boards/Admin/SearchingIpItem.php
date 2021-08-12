<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards\Admin;

use App\ToggleMenus\User\UserToggleMenu;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\User\Models\User;

class SearchingIpItem extends UserToggleMenu
{
    /** @var string */
    protected static $id = 'module/board@board/toggleMenu/board@searchingIpItem';

    /**
     * @return string
     */
    public static function getTitle(): string
    {
        return xe_trans('board::trackIP');
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return xe_trans('board::trackIP');
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return static::MENUTYPE_LINK;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        if ($board = Board::find($this->identifier)) {
            return route('settings.board.board.docs.index', [
                'search_target' => 'ip',
                'search_keyword' => $board->getAttribute('ipaddress'),
            ]);
        }

        return '';
    }

    /**
     * @return bool
     */
    public function allows(): bool
    {
        $user = auth()->user();
        return $user instanceof User ? $user->isAdmin() : false;
    }

    /**
     * @return string|null
     */
    public function getScript()
    {
        return null;
    }
}