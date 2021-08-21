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
            $instanceId = null;

            foreach ($query->getQuery()->wheres as $where) {
                $column = Arr::get($where, 'column');
                $value = Arr::get($where, 'value');

                if ($column === 'instance_id' && $value !== null) {
                    $instanceId = $value;
                    break;
                }
            }

            if (is_null($instanceId)) {
                $instanceId = InstanceConfig::instance() ? InstanceConfig::instance()->getInstanceId() : null;
            }

            $replyConfig = ReplyConfigHandler::make()->getActivated($instanceId);

            $query->when($replyConfig, function($query) {
                $query->where('parent_id', '')->with('replies');

                // 답변완료된 게시물만 보기. (has_adopted)
                $query->when(\request()->has('has_adopted'), function($query) {
                    $query->whereHas('data', function($dataQuery) { $dataQuery->whereNotNull('adopt_id'); });
                });

                // 답변이 완료가 안된 게시물만 보기. (has_not_adopted)
                $query->when(\request()->has('has_not_adopted'), function($query) {
                    $query->whereHas('data', function($dataQuery) { $dataQuery->whereNull('adopt_id'); });
                });
            });
        });
    }

    /**
     * intercept stored
     */
    public static function interceptStored()
    {
        $function = function ($function, Request $request, UserInterface $user, ConfigEntity $config, IdentifyManager $identifyManager) {
            $parentId = $request->get('parent_id');

            if (is_null($parentId)) {
                return $function($request, $user, $config, $identifyManager);
            }

            $replyConfig = ReplyConfigHandler::make()->getActivated($config->get('boardId'));
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
     * intercept updated
     */
    public static function interceptUpdated()
    {
        $function = function ($function, Board $item, Request $request, UserInterface $user, ConfigEntity $config, IdentifyManager $identifyManager) {
            $replyConfig = ReplyConfigHandler::make()->getActivated($item->instance_id);
            $isManager = app(BoardPermissionHandler::class)->checkManageAction($item->instance_id);

            if ($replyConfig !== null) {
                if($parentBoard = $item->findParentDoc()) {
                    if ($request->get('status') == Document::STATUS_NOTICE) {
                        throw new CanNotReplyNoticeException;   // 답글은 공지사항으로 설정할 수 없습니다.
                    }

                    if ($item->isAdopted($parentBoard)) {
                        if ($isManager === false) {
                            throw new CanNotUpdatedAdoptedException; // 채택된 글은 수정할 수 없습니다.
                        }
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

                else if ($item->existsReplies() === true) {
                    if ($replyConfig->get('protectUpdated', false) === true && $isManager === false) {
                        throw new CanNotUpdatedHasReplyException; // 답글이 적힌 글은 수정할 수 없습니다.
                    }
                }
            }

            return $function($item, $request, $user, $config, $identifyManager);
        };

        // update
        intercept(
            sprintf('%s@update', BoardService::class),
            sprintf('%s::reply__updated', BoardPlugin::getId()),
            $function
        );
    }

    /**
     * intercept deleted
     */
    public static function interceptDeleted()
    {
        $function = function ($function, Board $item, ConfigEntity $config) {
            $replyConfig = ReplyConfigHandler::make()->getActivated($item->instance_id);
            $isManager = app(BoardPermissionHandler::class)->checkManageAction($item->instance_id);

            if ($replyConfig !== null) {
                if ($parentBoard = $item->findParentDoc()) {
                    // set redirect url
                    $routeAction = request()->route()->getAction();

                    if (Arr::get($routeAction, 'module') === BoardModule::getId() && in_array(Arr::get($routeAction, 'as'), ['destroy', 'trash'])) {
                        request()->merge([
                            'redirect_url' => app(BoardUrlHandler::class)->getShow($parentBoard, request()->query->all()),
                            'redirect_message' => xe_trans('board::deletedReply')
                        ]);
                    }

                    // 채택된 질문을 삭제하는 경우..
                    if ($item->isAdopted($parentBoard)) {
                        if ($isManager === false) {
                            throw new CanNotDeletedAdoptedException; // 채택된 글은 삭제할 수 없습니다.
                        }

                        $parentBoard->getAttribute('data')->adopt_id = null;
                        $parentBoard->getAttribute('data')->adopt_at = null;
                        $parentBoard->getAttribute('data')->save();
                    }
                }

                else if ($item->existsReplies() === true) {
                    if ($replyConfig->get('protectDeleted', false) === true && $isManager === false) {
                        throw new CanNotDeleteHasReplyException; // 답글이 적힌 글은 삭제할 수 없습니다.
                    }
                }
            }

            return $function($item, $config);
        };

        // trash
        intercept(
            sprintf('%s@trash', BoardHandler::class),
            sprintf('%s::reply__trashed', BoardPlugin::getId()),
            $function
        );

        // remove
        intercept(
            sprintf('%s@remove', BoardHandler::class),
            sprintf('%s::reply__removed', BoardPlugin::getId()),
            $function
        );
    }
}


