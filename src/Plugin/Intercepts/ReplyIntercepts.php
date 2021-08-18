<?php

namespace Xpressengine\Plugins\Board\Plugin\Intercepts;

use Event;
use Illuminate\Support\Arr;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\User\UserInterface;
use Xpressengine\Plugins\Board\Exceptions\{
    CanNotDeleteHasReplyException,
    CanNotReplyNoticeException,
    CanNotUpdatedHasReplyException,
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
            if ($parentId = $request->get('parent_id')) {
                if ($config->get('replyPost', false) === false) {
                    throw new DisabledReplyException;
                }

                $parent = Board::findOrFail($parentId);
                if ($parent->isNotice()) {
                    throw new CanNotReplyNoticeException;
                }

                // set redirect url
                $routeAction = $request->route()->getAction();
                if (Arr::get($routeAction, 'module') === BoardModule::getId() && Arr::get($routeAction, 'as') === 'store') {
                    $request->merge([
                        'redirect_url' => app(BoardUrlHandler::class)->getShow($parent, $request->query->all()),
                        'redirect_message' => xe_trans('board::wroteReply')
                    ]);
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

            if ($item->hasParentDoc()) {
                $parent = Board::findOrFail($item->parent_id);

                // set redirect url
                $routeAction = $request->route()->getAction();

                if (Arr::get($routeAction, 'module') === BoardModule::getId() && Arr::get($routeAction, 'as') === 'update') {
                    $request->merge([
                        'redirect_url' => app(BoardUrlHandler::class)->getShow($parent, $request->query->all()),
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
            $instanceId = $item->instance_id;
            $replyConfig = $config->get('replyPost', false) ? ReplyConfigHandler::make()->get($instanceId) : null;

            if ($replyConfig !== null && $item->hasParentDoc()) {
               $parent = Board::findOrFail($item->parent_id);

               // set redirect url
                $routeAction = request()->route()->getAction();

                if (Arr::get($routeAction, 'module') === BoardModule::getId() && Arr::get($routeAction, 'as') === 'destroy') {
                    request()->merge([
                        'redirect_url' => app(BoardUrlHandler::class)->getShow($parent, request()->query->all()),
                        'redirect_message' => xe_trans('board::deletedReply')
                    ]);
                }
            }

            if (! app(BoardPermissionHandler::class)->checkManageAction($instanceId)) {
                if ($replyConfig !== null && $replyConfig->get('protectDeleted', false) && $item->existsReplies()) {
                    throw new CanNotDeleteHasReplyException;
                }
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


