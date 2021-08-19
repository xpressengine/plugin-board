<?php

namespace Xpressengine\Plugins\Board\Plugin\Intercepts;

use Event;
use Illuminate\Support\Arr;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\User\Models\User;
use Xpressengine\User\UserInterface;
use Xpressengine\Plugins\Board\Exceptions\{
    AlreadyAdoptedException,
    AlreadyRegisteredReplyException,
    CanNotDeletedAdoptedException,
    CanNotDeleteHasReplyException,
    CanNotReplyNoticeException,
    CanNotUpdatedAdoptedException,
    CanNotUpdatedHasReplyException,
    CantNotReplyOwnBoardException,
    DisabledReplyException
};
use Xpressengine\Plugins\Board\{
    BoardPermissionHandler,
    Components\Modules\BoardModule,
    ConfigHandler,
    Handler as BoardHandler,
    Plugin as BoardPlugin,
    IdentifyManager,
    ReplyConfigHandler,
    Services\BoardService,
    UrlHandler as BoardUrlHandler
};

abstract class ReplyIntercepts
{
    /**
     * listen reply articles
     */
    public static function listenReplyArticles()
    {
        Event::listen('xe.plugin.board.articles', function ($query) {
            $instanceConfig = InstanceConfig::instance();
            $instanceId = $instanceConfig->getInstanceId();

            $boardConfig = app(ConfigHandler::class)->get($instanceId);

            $query->when($boardConfig && $boardConfig->get('replyPost', false) === true, function($query) {
                $query->where('parent_id', '')->with('replies');
            });
        });
    }

    /**
     * intercept validate stored
     */
    public static function interceptValidateStored()
    {
        $function = function ($function, Request $request, UserInterface $user, ConfigEntity $config, IdentifyManager $identifyManager) {
            $parentId = $request->get('parent_id');

            if (is_null($parentId)) {
                return $function($request, $user, $config, $identifyManager);
            }

            $replyConfig = ReplyConfigHandler::make()->getByBoardConfig($config->get('boardId'));
            if (is_null($replyConfig)) {
                throw new DisabledReplyException; // 답글을 사용하지 않는 상태인 경우.
            }

            /** @var Board $parentBoard */
            $parentBoard = Board::with('replies', 'data')->findOrFail($parentId);

            if ($parentBoard->isNotice()) {
                throw new CanNotReplyNoticeException; // 공지에 답글을 작성할 수 없습니다.
            }

            if ($parentBoard->hasAdopted()) {
                throw new AlreadyAdoptedException; // 이미 채택된 답글이 있습니다.
            }

            $isParentAuthor = $user instanceof User && $user->getId() == $parentBoard->user_id;

            if ($replyConfig->get('blockAuthorSelf', false) === true && $isParentAuthor === true) {
                throw new CantNotReplyOwnBoardException; // 자신이 작성한 답글을 작성할 수 없습니다.
            }

            $alreadyReplyBoard = $parentBoard->getReplies()->first(function ($replyBoard) use ($user) {
                return $user instanceof User && $replyBoard->user_id == $user->getId();
            });

            if ($replyConfig->get('limitedOneTime', false) === true && $alreadyReplyBoard !== null) {
                throw new AlreadyRegisteredReplyException(); // 답변 작성은 한 게시물 당 한 번으로 제한합니다.
            }

            // set redirect url
            $routeAction = $request->route()->getAction();
            if (Arr::get($routeAction, 'module') === BoardModule::getId() && Arr::get($routeAction, 'as') === 'store') {
                $request->merge([
                    'redirect_url' => app(BoardUrlHandler::class)->getShow($parentBoard, $request->query->all()),
                    'redirect_message' => xe_trans('board::wroteReply')
                ]);
            }

            return $function($request, $user, $config, $identifyManager);
        };

        // store
        intercept(
            sprintf('%s@store', BoardService::class),
            sprintf('%s::reply__validateStored', BoardPlugin::getId()),
            $function
        );
    }

    /**
     * intercept validate updated
     */
    public static function interceptValidateUpdated()
    {
        $function = function ($function, Board $item, Request $request, UserInterface $user, ConfigEntity $config, IdentifyManager $identifyManager) {
            if ($item->hasParentDoc()) {
                if ($request->get('status') == Document::STATUS_NOTICE) {
                    throw new CanNotReplyNoticeException;
                }

                /** @var Board $parentBoard */
                $parentBoard = Board::with('data')->findOrFail($item->parent_id);

                if ($item->isAdopted($parentBoard) === true) {
                    throw new CanNotUpdatedAdoptedException; // 채택된 글은 수정할 수 없습니다.
                }

                // set redirect url
                $routeAction = $request->route()->getAction();

                if (Arr::get($routeAction, 'module') === BoardModule::getId() && Arr::get($routeAction, 'as') === 'update') {
                    $request->merge([
                        'redirect_url' => app(BoardUrlHandler::class)->getShow($parentBoard, $request->query->all()),
                        'redirect_message' => xe_trans('board::updatedReply')
                    ]);
                }
            }

            return $function($item, $request, $user, $config, $identifyManager);
        };

        // update
        intercept(
            sprintf('%s@update', BoardService::class),
            sprintf('%s::reply__validateUpdated', BoardPlugin::getId()),
            $function
        );
    }

    /**
     * intercept protect deleted
     */
    public static function interceptProtectDeleted()
    {
        $function = function ($function, Board $item, ConfigEntity $config, IdentifyManager $identifyManager) {
            /** @var Board $parentBoard */
            $parentBoard = null;
            $replyConfig = ReplyConfigHandler::make()->getByBoardConfig($item->instance_id);

            if ($replyConfig !== null && $item->hasParentDoc()) {
                $parentBoard = Board::with('data')->findOrFail($item->parent_id);

                // set redirect url
                $routeAction = request()->route()->getAction();

                if (Arr::get($routeAction, 'module') === BoardModule::getId() && Arr::get($routeAction, 'as') === 'destroy') {
                    request()->merge([
                        'redirect_url' => app(BoardUrlHandler::class)->getShow($parentBoard, request()->query->all()),
                        'redirect_message' => xe_trans('board::deletedReply')
                    ]);
                }
            }

            if ($replyConfig !== null && app(BoardPermissionHandler::class)->checkManageAction($item->instance_id) === false) {
                if ($replyConfig->get('protectDeleted', false) && $item->existsReplies()) {
                    throw new CanNotDeleteHasReplyException; // 답글이 적힌 글은 삭제할 수 없습니다.
                }

                if ($parentBoard !== null && $item->isAdopted($parentBoard)) {
                    throw new CanNotDeletedAdoptedException; // 채택된 글은 삭제할 수 없습니다.
                }
            }

            if ($parentBoard !== null && $item->isAdopted($parentBoard)) {
                $parentBoard->getAttribute('data')->adopt_id = null;
                $parentBoard->getAttribute('data')->adopt_at = null;
                $parentBoard->getAttribute('data')->save();
            }

           return $function($item, $config, $identifyManager);
        };

        // destroy
        intercept(
            sprintf('%s@destroy', BoardService::class),
            sprintf('%s::reply__protectDestroyed', BoardPlugin::getId()),
            $function
        );
    }

    /**
     * intercept protect updated
     */
    public static function interceptProtectUpdated()
    {
        // put
        intercept(
            sprintf('%s@put', BoardHandler::class),
            sprintf('%s::reply__putLimit', BoardPlugin::getId()),
            function ($function, Board $board, array $args, ConfigEntity $config) {
                if (app(BoardPermissionHandler::class)->checkManageAction($board->instance_id)) {
                    return $function($board, $args, $config);
                }

                $replyConfig =  ReplyConfigHandler::make()->getByBoardConfig($board->instance_id);

                if ($replyConfig !== null && $replyConfig->get('protectUpdated', false)) {
                    if ($board->existsReplies()) {
                        throw new CanNotUpdatedHasReplyException;
                    }
                }

                return $function($board, $args, $config);
            }
        );
    }
}


