<?php
namespace Xpressengine\Plugins\Board;

use Illuminate\Support\Facades\Gate;
use Xpressengine\Permission\Instance;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Services\BoardService;
use Xpressengine\Plugins\Comment\Models\Comment;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;

class CommentObserver
{
    protected $service;

    protected $boardPermission;

    public function __construct(BoardService $service, BoardPermissionHandler $boardPermission)
    {
        $this->service = $service;
        $this->boardPermission = $boardPermission;
    }

    public function retrieved(Comment $comment)
    {
        $item = $comment->getTarget();
        if ($item instanceof Board) {
            $identifyManager = app('xe.board.identify');
            if ($this->service->hasItemPerm($item, auth()->user(), $identifyManager, $this->isManager($item)) == false
                && Gate::denies(
                    BoardPermissionHandler::ACTION_READ,
                    new Instance($this->boardPermission->name($item->getInstanceId()))
                )
            ) {
                throw new AccessDeniedHttpException;
            }
        }
    }

    public function creating(Comment $comment)
    {
        $item = $comment->getTarget();
        if ($item instanceof Board) {
            if (!$item->allow_comment) {
                abort(500, xe_trans('comment::notAllowedComment'));
            }
        }
    }

    protected function isManager(Board $item)
    {
        $boardPermission = app('xe.board.permission');
        return Gate::allows(
            BoardPermissionHandler::ACTION_MANAGE,
            new Instance($boardPermission->name($item->getInstanceId()))
        ) ? true : false;
    }
}
