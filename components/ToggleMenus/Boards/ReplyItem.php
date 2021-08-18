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
        $board = Board::with('replies')->findOrFail($this->identifier);

        if ($board->isNotice()|| $board->hasParentDoc()) {
            return false;
        }

        $configHandler = app('xe.board.config');
        $boardPermission = app('xe.board.permission');

        // board's config
        $config = $configHandler->get($this->instanceId);
        if (is_null($config)) {
            return false;
        }

        // board reply's config
        $replyConfig = $config->get('replyPost', false) ? ReplyConfigHandler::make()->get($this->instanceId) : null;
        if (is_null($replyConfig)) {
            return false;
        }

        // 자신이 작성한 게시물에 답글을 작성할 수 없습니다.
        if ($replyConfig->get('blockAuthorSelf', false) && $board->user_id == auth()->id()) {
            return false;
        }

        // 이미 작성된 답글이 있는 경우.
        if ($replyConfig->get('limitedOneTime', false)) {
            $replyBoard = $board->getReplies()->first(function($replyBoard) {
                return $replyBoard->user_id === auth()->id();
            });

            if ($replyBoard !== null) {
                return false;
            }
        }

        return $boardPermission->checkCreateAction($this->instanceId);
    }
}