<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\ReplyConfigHandler;
use Xpressengine\ToggleMenu\AbstractToggleMenu;

class ReplyItem extends AbstractToggleMenu
{
    /** @var string */
    protected static $id = 'module/board@board/toggleMenu/board@replyItem';

    public static function getTitle(): string
    {
        return xe_trans('board::reply');
    }

    public function getText(): string
    {
        return xe_trans('board::reply');
    }

    public function getType(): string
    {
        return static::MENUTYPE_LINK;
    }

    public function getAction(): string
    {
        return instance_route('create', ['parent_id' => $this->identifier], $this->instanceId);
    }

    public function getScript()
    {
        return null;
    }

    public function allows(): bool
    {
        /** @var Board $board */
        $board = Board::with('replies', 'data')->findOrFail($this->identifier);

        if ($board->isNotice() || $board->hasParentDoc() || $board->hasAdopted()) {
            return false;
        }

        if ($replyConfig = ReplyConfigHandler::make()->getActivated($this->instanceId)) {
            if (($replyConfig->get('blockAuthorSelf', false) && $board->user_id == auth()->id())) {
                return false;
            }

            $replyBoard = $board->getReplies()->first(function($replyBoard) {
                return $replyBoard->user_id === auth()->id();
            });

            if ($replyConfig->get('limitedOneTime', false) && $replyBoard !== null) {
                return false;
            }
        }

        return app('xe.board.permission')->checkCreateAction($this->instanceId);
    }
}
