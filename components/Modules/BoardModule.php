<?php
/**
 * BoardModule
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Board\Components\Modules;

use Route;
use XeSkin;
use View;
use Mail;
use Xpressengine\Menu\AbstractModule;
use Xpressengine\Plugins\Board\Handler as BoardHandler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Models\Board as BoardModel;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Plugins\Comment\Handler as CommentHandler;
use Xpressengine\Plugins\Comment\Models\Comment;
use Xpressengine\Plugins\Comment\Models\Target as CommentTarget;

/**
 * BoardModule
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class BoardModule extends AbstractModule
{
    const FILE_UPLOAD_PATH = 'public/plugin/board';
    const THUMBNAIL_TYPE = 'spill';

    /**
     * boot
     *
     * @return void
     */
    public static function boot()
    {
        self::registerArchiveRoute();
        self::registerSettingsRoute();
        self::registerInstanceRoute();
        self::registerSettingsMenu();
        self::registerCommentCountIntercept();
        if (app()->runningInConsole() === false) {
            self::registerCommentAlarmIntercept();
            self::registerManagerAlarmIntercept();
        }
    }

    /**
     * register plugin archive route
     *
     * @return void
     */
    protected static function registerArchiveRoute()
    {
        // set routing
        config(['xe.routing' => array_merge(
            config('xe.routing'),
            ['board_archives' => 'archives']
        )]);

        Route::group([
            'prefix' => 'archives',
            'namespace' => 'Xpressengine\Plugins\Board\Controllers'
        ], function () {
            Route::get('/{slug}', ['as' => 'archives', 'uses' => 'ArchivesController@index']);
        });
    }

    /**
     * Register Plugin Manage Route
     *
     * @return void
     */
    protected static function registerSettingsRoute()
    {
        Route::settings(self::getId(), function () {
            // global
            Route::get('/', ['as' => 'settings.board.board.index', 'uses' => 'BoardSettingsController@index']);
            Route::get(
                '/global/config',
                ['as' => 'settings.board.board.global.config', 'uses' => 'BoardSettingsController@editGlobalConfig']
            );
            Route::post(
                '/global/config/update',
                ['as' => 'settings.board.board.global.config.update', 'uses' => 'BoardSettingsController@updateGlobalConfig']
            );
            Route::get(
                '/global/permission',
                ['as' => 'settings.board.board.global.permission', 'uses' => 'BoardSettingsController@editGlobalPermission']
            );
            Route::post(
                '/global/permission/update',
                ['as' => 'settings.board.board.global.permission.update', 'uses' => 'BoardSettingsController@updateGlobalPermission']
            );
            Route::get(
                '/global/toggleMenu',
                ['as' => 'settings.board.board.global.toggleMenu', 'uses' => 'BoardSettingsController@editGlobalToggleMenu']
            );

            // module
            Route::get('config/{boardId}', ['as' => 'settings.board.board.config', 'uses' => 'BoardSettingsController@editConfig']);
            Route::post(
                'config/update/{boardId}',
                ['as' => 'settings.board.board.config.update', 'uses' => 'BoardSettingsController@updateConfig']
            );
            Route::get('permission/{boardId}', ['as' => 'settings.board.board.permission', 'uses' => 'BoardSettingsController@editPermission']);
            Route::post(
                'permission/update/{boardId}',
                ['as' => 'settings.board.board.permission.update', 'uses' => 'BoardSettingsController@updatePermission']
            );
            Route::get('skin/edit/{boardId}', ['as' => 'settings.board.board.skin', 'uses' => 'BoardSettingsController@editSkin']);
            Route::get('editor/edit/{boardId}', ['as' => 'settings.board.board.editor', 'uses' => 'BoardSettingsController@editEditor']);
            Route::get('columns/edit/{boardId}', ['as' => 'settings.board.board.columns', 'uses' => 'BoardSettingsController@editColumns']);
            Route::post('columns/update/{boardId}', ['as' => 'settings.board.board.columns.update', 'uses' => 'BoardSettingsController@updateColumns']);
            Route::get('dynamicField/edit/{boardId}', ['as' => 'settings.board.board.dynamicField', 'uses' => 'BoardSettingsController@editDynamicField']);
            Route::get('toggleMenu/edit/{boardId}', ['as' => 'settings.board.board.toggleMenu', 'uses' => 'BoardSettingsController@editToggleMenu']);

            Route::post('storeCategory/', [
                'as' => 'settings.board.board.storeCategory', 'uses' => 'BoardSettingsController@storeCategory'
            ]);

            // docs
            Route::get('docs', [
                'as' => 'settings.board.board.docs.index',
                'uses' => 'BoardSettingsController@docsIndex',
                'settings_menu' => 'contents.board.board'
            ]);
            Route::get('docs/trash', [
                'as' => 'settings.board.board.docs.trash',
                'uses' => 'BoardSettingsController@docsTrash',
                'settings_menu' => 'contents.board.boardtrash'
            ]);
            Route::post('approve', ['as' => 'settings.board.board.approve', 'uses' => 'BoardSettingsController@approve']);
            Route::post('copy', ['as' => 'settings.board.board.copy', 'uses' => 'BoardSettingsController@copy']);
            Route::post('destroy', ['as' => 'settings.board.board.destroy', 'uses' => 'BoardSettingsController@destroy']);
            Route::post('trash', ['as' => 'settings.board.board.trash', 'uses' => 'BoardSettingsController@trash']);
            Route::post('move', ['as' => 'settings.board.board.move', 'uses' => 'BoardSettingsController@move']);
            Route::post('restore', ['as' => 'settings.board.board.restore', 'uses' => 'BoardSettingsController@restore']);
        }, ['namespace' => 'Xpressengine\Plugins\Board\Controllers']);
    }

    /**
     * Register Plugin Instance Route
     *
     * @return void
     */
    protected static function registerInstanceRoute()
    {
        Route::instance(self::getId(), function () {
            Route::get('/', ['as' => 'index', 'uses' => 'BoardModuleController@index']);
            Route::get('/show/{id}', ['as' => 'show', 'uses' => 'BoardModuleController@show']);
            Route::get('/print/{id}', ['as' => 'print', 'uses' => 'BoardModuleController@print']);

            Route::get('/articles', ['as' => 'api.articles', 'uses' => 'BoardModuleController@articles']);
            Route::get('/notices', ['as' => 'api.notices', 'uses' => 'BoardModuleController@notices']);
            Route::get('/articles/{id}', ['as' => 'api.article', 'uses' => 'BoardModuleController@get']);

            Route::get('/create', ['as' => 'create', 'uses' => 'BoardModuleController@create']);
            Route::post('/store', ['as' => 'store', 'uses' => 'BoardModuleController@store']);

            Route::get('/edit/{id}', ['as' => 'edit', 'uses' => 'BoardModuleController@edit']);
            Route::post('/update', ['as' => 'update', 'uses' => 'BoardModuleController@update']);

            Route::delete('/destroy/{id}', ['as' => 'destroy', 'uses' => 'BoardModuleController@destroy']);

            Route::get('/guest/id/{id}', ['as' => 'guest.id', 'uses' => 'BoardModuleController@guestId']);
            Route::post('/guest/certify/{id}', [
                'as' => 'guest.certify', 'uses' => 'BoardModuleController@guestCertify'
            ]);

            Route::get('/revision/{id}', ['as' => 'revision', 'uses' => 'BoardModuleController@revision']);

            Route::post('/preview', ['as' => 'preview', 'uses' => 'BoardModuleController@preview']);
            Route::post('/temporary', ['as' => 'temporary', 'uses' => 'BoardModuleController@temporary']);
            Route::post('/trash/{id}', ['as' => 'trash', 'uses' => 'BoardModuleController@trash']);

            Route::post('/vote/{option}/{id}', ['as' => 'vote', 'uses' => 'BoardModuleController@vote']);
            Route::get('/vote/show/{id}', ['as' => 'showVote', 'uses' => 'BoardModuleController@showVote']);
            Route::get('/vote/users/{option}/{id}', [
                'as' => 'votedUsers', 'uses' => 'BoardModuleController@votedUsers'
            ]);
            Route::get('/vote/modal/{option}/{id}', [
                'as' => 'votedModal', 'uses' => 'BoardModuleController@votedModal'
            ]);
            Route::get('/vote/userList/{option}/{id}', [
                'as' => 'votedUserList', 'uses' => 'BoardModuleController@votedUserList'
            ]);

            Route::post('/favorite/{id}', ['as' => 'favorite', 'uses' => 'BoardModuleController@favorite']);

            Route::get('/hasSlug', ['as' => 'hasSlug', 'uses' => 'BoardModuleController@hasSlug']);
            Route::get('/{slug}', ['as' => 'slug', 'uses' => 'BoardModuleController@slug']);
        }, ['namespace' => 'Xpressengine\Plugins\Board\Controllers']);

        BoardSlug::setReserved([
            'index', 'create', 'edit', 'destroy', 'show', 'identify', 'revision', 'store', 'preview', 'temporary',
            'trash', 'certify', 'update', 'vote', 'manageMenus', 'comment', 'file', 'suggestion', 'slug', 'hasSlug',
            'favorite'
        ]);
    }

    /**
     * register interception
     *
     * @return void
     */
    public static function registerSettingsMenu()
    {
        // settings menu 등록
        $menus = [
            'contents.board' => [
                'title' => 'board::board',
                'display' => true,
                'description' => '',
                'ordering' => 2000
            ],
            'contents.board.board' => [
                'title' => 'board::articlesManage',
                'display' => true,
                'description' => '',
                'ordering' => 2001
            ],
            'contents.board.boardtrash' => [
                'title' => 'board::trashManage',
                'display' => true,
                'description' => '',
                'ordering' => 2003
            ],
        ];
        foreach ($menus as $id => $menu) {
            app('xe.register')->push('settings/menu', $id, $menu);
        }
    }

    /**
     * register intercept for comment count
     *
     * @return void
     */
    public static function registerCommentCountIntercept()
    {
        intercept(
            sprintf('%s@create', CommentHandler::class),
            static::class.'-comment-create',
            function ($func, array $inputs, $user = null) {
                $comment = $func($inputs, $user);

                self::setBoardCommentCount($comment->target->target_id);

                return $comment;
            }
        );

        intercept(
            sprintf('%s@trash', CommentHandler::class),
            static::class.'-comment-trash',
            function ($func, Comment $comment) {
                $result = $func($comment);

                self::setBoardCommentCount($comment->target->target_id);

                return $result;
            }
        );

        intercept(
            sprintf('%s@remove', CommentHandler::class),
            static::class.'-comment-remove',
            function ($func, Comment $comment) {
                $result = $func($comment);

                self::setBoardCommentCount($comment->target->target_id);

                return $result;
            }
        );

        intercept(
            sprintf('%s@restore', CommentHandler::class),
            static::class.'-comment-restore',
            function ($func, Comment $comment) {
                $result = $func($comment);

                self::setBoardCommentCount($comment->target->target_id);

                return $result;
            }
        );

        intercept(
            sprintf('%s@approve', CommentHandler::class),
            static::class.'-comment-approve',
            function ($func, Comment $comment) {
                $result = $func($comment);

                self::setBoardCommentCount($comment->target->target_id);

                return $result;
            }
        );

        intercept(
            sprintf('%s@reject', CommentHandler::class),
            static::class.'-comment-reject',
            function ($func, Comment $comment) {
                $result = $func($comment);

                self::setBoardCommentCount($comment->target->target_id);

                return $result;
            }
        );
    }

    protected static function setBoardCommentCount($boardId)
    {
        if ($board = BoardModel::find($boardId)) {
            if ($board == null) {
                return;
            }
            if ($board->type != static::getId()) {
                return;
            }

            $commentCount = $board->comments()
                ->where('approved', Comment::APPROVED_APPROVED)
                ->where('status', '<>', Comment::STATUS_TRASH)
                ->where('display', '<>', Comment::DISPLAY_HIDDEN)
                ->count();

            $board->comment_count = $commentCount;
            $board->save();
        }
    }

    /**
     * register intercept ofr comment alarm
     *
     * @return void
     */
    public static function registerCommentAlarmIntercept()
    {
        intercept(
            sprintf('%s@create', CommentHandler::class),
            static::class.'-comment-alarm',
            function ($func, $inputs, $user = null) {
                $comment = $func($inputs, $user);

                $board = BoardModel::find($comment->target->target_id);

                if ($board == null) {
                    return $comment;
                }
                if ($board->type != static::getId()) {
                    return $comment;
                }
                if ($board->boardData->isAlarm() === false) {
                    return $comment;
                }

                /** @var UrlHandler $urlHandler */
                $urlHandler = app('xe.board.url');
                $urlHandler->setConfig(app('xe.board.config')->get($board->instance_id));
                $url = $urlHandler->getShow($board);
                $parts = parse_url($url);
                $semanticUrl = sprintf('%s://%s%s', $parts['scheme'], $parts['host'], $parts['path']);

                $data = [
                    'title' => xe_trans('board::newCommentRegistered'),
                    'contents' => sprintf(
                        '<a href="%s" target="_blank">%s</a><br/>%s<br/><br/><br/>%s',
                        $url,
                        $semanticUrl,
                        xe_trans(
                            'board::newCommentRegisteredBy',
                            ['displayName' => $comment->getAuthor()->getDisplayName()]
                        ),
                        $comment->pure_content
                    ),
                ];

                $emails = [];

                // writer email
                if ($board->email != null && $board->email != '') {
                    $emails[] = $board->email;
                } else {
                    $emails[] = $board->user->email;
                }

                // comment writers
                $model = Comment::division($comment->instance_id);
                $query = $model->whereHas('target', function ($query) use ($board) {
                    $query->where('target_id', $board->id);
                })
                ->where('display', '!=', Comment::DISPLAY_HIDDEN);
                $comments = $query->get();
                foreach ($comments as $dstComment) {
                    if ($dstComment->email != null && $dstComment->email != '') {
                        $emails[] = $dstComment->email;
                    } else {
                        $emails[] = $dstComment->user->email;
                    }
                }

                $emails = array_unique($emails);

                foreach ($emails as $toMail) {
                    if (!$toMail) {
                        continue;
                    }

                    if ($comment->email != null && $comment->email != '') {
                        $writerMail = $comment->email;
                    } else {
                        $writerMail = $comment->user->email;
                    }

                    if ($toMail == $writerMail) {
                        continue;
                    }

                    send_notice_email('new_comment', $toMail, $data['title'], $data['contents'], function ($notifiable) use ($board) {
                        $menuItem = app('xe.menu')->getItem($board->instance_id);
                        $subject = sprintf('Comment:[%s] %s', xe_trans($menuItem->title), $board->title);
                        return $subject;
                    });

                }



                return $comment;
            }
        );
    }

    /**
     * register intercept for manager alarm
     *
     * @return void
     */
    public static function registerManagerAlarmIntercept()
    {
        intercept(
            sprintf('%s@add', BoardHandler::class),
            static::class .'-manager-board-alarm',
            function ($func, $args, $user, $config) {
                $board = $func($args, $user, $config);

                /** @var ConfigHandler $configHandler */
                $configHandler = app('xe.board.config');
                $config = $configHandler->get($board->instance_id);
                if ($config == null) {
                    return $board;
                }

                if ($config->get('managerEmail', '') === '') {
                    return $board;
                }

                $managerEmails = explode(',', trim($config->get('managerEmail')));
                if (count($managerEmails) == 0) {
                    return $board;
                }

                /** @var UrlHandler $urlHandler */
                $urlHandler = app('xe.board.url');
                $urlHandler->setConfig($config);
                $url = $urlHandler->getShow($board);
                $parts = parse_url($url);
                $semanticUrl = sprintf('%s://%s%s', $parts['scheme'], $parts['host'], $parts['path']);

                $data = [
                    'title' => xe_trans('board::newPostsRegistered'),
                    'contents' => sprintf(
                        '<span>%s : %s</span> <a href="%s" target="_blank">%s</a><br/><br/><br/>%s',
                        xe_trans('xe::writer'),
                        $board->getDisplayWriterName(),
                        $url,
                        $semanticUrl,
                        $board->pure_content
                    ),
                ];

                foreach ($managerEmails as $toMail) {
                    if (!$toMail) {
                        continue;
                    }

                    send_notice_email('new_article', $toMail, $data['title'], $data['contents'], function ($notifiable) use ($board) {
                        $applicationName = xe_trans(app('xe.site')->getSiteConfig()->get('site_title'));

                        $menuItem = app('xe.menu')->getItem($board->instance_id);
                        $subject = sprintf(
                            '[%s - %s] %s',
                            $applicationName,
                            xe_trans($menuItem->title),
                            xe_trans('board::newPostsRegistered')
                        );
                        return $subject;
                    });

                }

                return $board;
            }
        );
    }

    /**
     * get manage URI
     *
     * @return string
     */
    public static function getSettingsURI()
    {
        return route('settings.board.board.global.config');
    }

    /**
     * this module is route able
     *
     * @return bool
     */
    public static function isRouteAble()
    {
        return true;
    }

    /**
     * Return Create Form View
     *
     * @return string
     */
    public function createMenuForm()
    {
        $skins = XeSkin::getList('module/board@board');

        return View::make('board::components/Modules/views/create', [
            'boardId' => null,
            'config' => app('xe.board.config')->getDefault(),
            'skins' => $skins,
            'handler' => app('xe.board.handler'),
        ])->render();
    }

    /**
     * Process to Store
     *
     * @param string $instanceId     instance id
     * @param array  $menuTypeParams menu type parameters
     * @param array  $itemParams     item parameters
     * @return void
     */
    public function storeMenu($instanceId, $menuTypeParams, $itemParams)
    {
        $input = $menuTypeParams;
        $input['boardId'] = $instanceId;

        app('xe.board.instance')->create($input);
        app('xe.editor')->setInstance($instanceId, 'editor/ckeditor@ckEditor');
    }

    /**
     * Return Edit Form View
     *
     * @param string $instanceId instance id
     * @return string
     */
    public function editMenuForm($instanceId)
    {
        $skins = XeSkin::getList(self::getId());

        return View::make('board::components/Modules/views/edit', [
            'boardId' => $instanceId,
            'config' => app('xe.board.config')->get($instanceId),
            'skins' => $skins,
            'handler' => app('xe.board.handler'),
        ])->render();
    }

    /**
     * Process to Update
     *
     * @param string $instanceId     instance id
     * @param array  $menuTypeParams menu type parameters
     * @param array  $itemParams     item parameters
     * @return void
     */
    public function updateMenu($instanceId, $menuTypeParams, $itemParams)
    {
        $menuTypeParams['boardId'] = $instanceId;

        app('xe.board.instance')->updateConfig($menuTypeParams);
    }

    /**
     * Process to delete
     *
     * @param string $instanceId instance id
     * @return void
     */
    public function deleteMenu($instanceId)
    {
        app('xe.board.instance')->destroy($instanceId);
    }

    /**
     * summary
     *
     * @param string $instanceId instance id
     * @return string
     */
    public function summary($instanceId)
    {
        return xe_trans(
            'board::destroySummary',
            app('xe.board.instance')->summary($instanceId, app('xe.board.handler'))
        );
    }

    /**
     * Return URL about module's detail setting
     * getInstanceSettingURI
     *
     * @param string $instanceId instance id
     * @return mixed
     */
    public static function getInstanceSettingURI($instanceId)
    {
        return route('settings.board.board.config', $instanceId);
    }

    /**
     * Get menu type's item object
     *
     * @param string $id item id of menu type
     * @return mixed
     */
    public function getTypeItem($id)
    {
        static $items = [];

        if (!isset($items[$id])) {
            $items[$id] = \Xpressengine\Plugins\Board\Models\Board::find($id);
        }

        return $items[$id];
    }
}
