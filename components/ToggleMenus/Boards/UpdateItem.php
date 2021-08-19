<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\ReplyConfigHandler;
use Xpressengine\ToggleMenu\AbstractToggleMenu;

class UpdateItem extends AbstractToggleMenu
{
    /** @var string */
    protected static $id = 'module/board@board/toggleMenu/board@updateItem';

    public static function getTitle(): string
    {
        return xe_trans('xe::update');
    }

    public function getText(): string
    {
        return xe_trans('xe::update');
    }

    public function getType(): string
    {
        return static::MENUTYPE_LINK;
    }

    public function getAction(): string
    {
        $params = array_merge(request()->all(), ['id' => $this->identifier]);
        return instance_route('edit', $params, $this->instanceId);
    }

    public function getScript()
    {
        return null;
    }

    public function allows(): bool
    {
        /** @var Board $board */
        $board = Board::findOrFail($this->identifier);

        if (app('xe.board.permission')->checkManageAction($this->instanceId)) {
            return true;
        }

        if ($replyConfig = ReplyConfigHandler::make()->getByBoardConfig($this->instanceId)) {
            if (($replyConfig->get('protectUpdated', false) && $board->existsReplies()) || $board->isAdopted()) {
                return false;
            }
        }

        return $board->user_id === auth()->id();
    }
}
