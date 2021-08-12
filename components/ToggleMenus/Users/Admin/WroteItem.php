<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Users\Admin;

use App\ToggleMenus\User\UserToggleMenu;
use Xpressengine\User\Models\User;

class WroteItem extends UserToggleMenu
{
    /**
     * @return string
     */
    public static function getTitle(): string
    {
        return xe_trans('board::trackWrote');
    }

    /** @var string */
    protected static $id = 'user/toggleMenu/board@adminWroteItem';

    /**
     * @return string
     */
    public function getText(): string
    {
        return xe_trans('board::trackWrote');
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
        if ($user = User::find($this->identifier)) {
            return route('settings.board.board.docs.index', [
                'search_target' => 'writerId',
                'search_keyword' => $user->getAttribute('login_id'),
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