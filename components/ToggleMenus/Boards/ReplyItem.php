<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Xpressengine\Plugins\Board\Models\Board;
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
        $board = Board::findOrFail($this->identifier);

        if ($board->isNotice()|| $board->hasParentDoc()) {
            return false;
        }

        $configHandler = app('xe.board.config');
        $boardPermission = app('xe.board.permission');

        $config = $configHandler->get($this->instanceId);
        if (is_null($config)) {
            return false;
        }

        return $config->get('replyPost', false) ? $boardPermission->checkCreateAction($this->instanceId) : false;
    }
}