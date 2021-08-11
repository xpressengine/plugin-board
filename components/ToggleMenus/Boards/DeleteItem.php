<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Plugin as BoardPlugin;
use Xpressengine\ToggleMenu\AbstractToggleMenu;

class DeleteItem extends AbstractToggleMenu
{
    /** @var string */
    protected static $id = 'module/board@board/toggleMenu/board@deleteItem';

    public function __construct()
    {
        \XeFrontend::translation(['board::msgDeleteConfirm']);
    }

    public static function getTitle(): string
    {
        return xe_trans('xe::delete');
    }

    public function getText(): string
    {
        return xe_trans('xe::delete');
    }

    public function getType(): string
    {
        return static::MENUTYPE_EXEC;
    }

    public function getAction(): string
    {
        $url = app('xe.board.url')->get('destroy', ['id' => $this->identifier], $this->instanceId);
        return sprintf('BoardToggleMenu.delete(event, "%s")', $url);
    }

    public function getScript(): string
    {
        return BoardPlugin::asset('assets/js/src/toggleMenu.js');
    }

    public function allows(): bool
    {
        $board = Board::findOrFail($this->identifier);

        if ($board->getAttribute('user_id') === auth()->id()) {
            return true;
        }

        $configHandler = app('xe.board.config');
        $boardPermission = app('xe.board.permission');

        $config = $configHandler->get($this->instanceId);
        return $config !== null ? $boardPermission->checkManageAction($this->instanceId) : false;
    }
}
