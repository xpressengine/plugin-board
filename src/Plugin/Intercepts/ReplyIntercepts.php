<?php

namespace Xpressengine\Plugins\Board\Plugin\Intercepts;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\User\UserInterface;
use Xpressengine\Plugins\Board\Exceptions\{
    CanNotDeleteHasReplyException,
    CanNotReplyNoticeException,
    CanNotUpdatedHasReplyException,
    DisabledReplyException
};
use Xpressengine\Plugins\Board\{
    BoardPermissionHandler,
    Handler as BoardHandler,
    Plugin as BoardPlugin,
    IdentifyManager,
    ReplyConfigHandler,
    Services\BoardService
};

abstract class ReplyIntercepts
{
    /**
     * intercept validate stored
     */
    public static function interceptValidateStored()
    {
        $function = function ($function, Request $request, UserInterface $user, ConfigEntity $config, IdentifyManager $identifyManager) {
            if ($parentId = $request->get('parent_id')) {
                if ($config->get('replyPost', false) === false) {
                    throw new DisabledReplyException;
                }

                if (Board::findOrFail($parentId)->isNotice()) {
                    throw new CanNotReplyNoticeException;
                }
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
            if ($request->get('status') == Document::STATUS_NOTICE && $item->hasParentDoc()) {
                throw new CanNotReplyNoticeException;
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
        $function = function ($function, Board $item, ConfigEntity $config) {
            $instanceId = $item->instance_id;
            $permissionHandler = app(BoardPermissionHandler::class);

            if ($permissionHandler->checkManageAction($instanceId)) {
                return $function($item, $config);
            }

           $replyConfig = $config->get('replyPost', false) ? ReplyConfigHandler::make()->get($instanceId) : null;

           if ($replyConfig !== null && $replyConfig->get('protectDeleted', false)) {
               if ($item->existsReplies()) {
                   throw new CanNotDeleteHasReplyException;
               }
           }

            return $function($item, $config);
        };

        // remove
        intercept(
            sprintf('%s@remove', BoardHandler::class),
            sprintf('%s::reply__removedLimit', BoardPlugin::getId()),
            $function
        );

        // trash
        intercept(
            sprintf('%s@trash', BoardHandler::class),
            sprintf('%s::reply__trashedLimit', BoardPlugin::getId()),
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
                $instanceId = $board->instance_id;
                $permissionHandler = app(BoardPermissionHandler::class);

                if ($permissionHandler->checkManageAction($instanceId)) {
                    return $function($board, $args, $config);
                }

                $replyConfig = $config->get('replyPost', false) ? ReplyConfigHandler::make()->get($instanceId) : null;

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


