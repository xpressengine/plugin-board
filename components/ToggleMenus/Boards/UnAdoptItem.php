<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Plugin as BoardPlugin;
use Xpressengine\Plugins\Board\ReplyConfigHandler;
use Xpressengine\ToggleMenu\AbstractToggleMenu;

class UnAdoptItem extends AbstractToggleMenu
{
    /** @var string */
    protected static $id = 'module/board@board/toggleMenu/board@unAdoptItem';

    public static function getTitle(): string
    {
        return xe_trans('채택 취소');
    }

    public function getText(): string
    {
        return xe_trans('채택 취소');
    }

    public function getType(): string
    {
        return static::MENUTYPE_EXEC;
    }

    public function getAction(): string
    {
        $url = app('xe.board.url')->get('unAdopt', ['id' => $this->identifier], $this->instanceId);
        return sprintf('BoardToggleMenu.unAdopt(event, "%s")', $url);
    }

    public function getScript(): string
    {
        return BoardPlugin::asset('assets/js/src/toggleMenu.js');
    }

    public function allows(): bool
    {
        /** @var Board $board */
        $board = Board::findOrFail($this->identifier);

        $replyConfig = ReplyConfigHandler::make()->getActivated($this->instanceId);
        $parentBoard = $board->findParentDoc();

        if (is_null($replyConfig) || is_null($parentBoard)) {
            return false;
        }

        return $board->isAdopted($parentBoard) === true && $parentBoard->user_id === auth()->id();
    }
}