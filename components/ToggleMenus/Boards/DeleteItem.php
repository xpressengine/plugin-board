<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Plugin as BoardPlugin;
use Xpressengine\Plugins\Board\ReplyConfigHandler;
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
        /** @var Board $board */
        $board = Board::findOrFail($this->identifier);

        if (app('xe.board.permission')->checkManageAction($this->instanceId)) {
            return true;
        }

        if ($replyConfig = ReplyConfigHandler::make()->getByBoardConfig($this->instanceId)) {
            if (($replyConfig->get('protectDeleted', false) && $board->existsReplies()) || $board->isAdopted()) {
                return false;
            }
        }

        return $board->user_id === auth()->id();
    }
}
