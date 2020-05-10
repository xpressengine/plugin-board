<?php
/**
 * Resources
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Board\Plugin;

use Illuminate\Support\Facades\Gate;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Config\ConfigManager;
use Xpressengine\DynamicField\DynamicFieldHandler;
use Xpressengine\Permission\Instance;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\Components\Skins\Board\NewCommon\NewCommonSkin;
use Xpressengine\Plugins\Board\Components\UIObjects\NewTitle\NewTitleUIObject;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Exceptions\AlreadyUseCategoryHttpException;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\IdentifyManager;
use Xpressengine\Plugins\Board\InstanceManager;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Models\BoardCategory;
use Xpressengine\Plugins\Board\Plugin;
use Xpressengine\Plugins\Board\RecycleBin;
use Xpressengine\Plugins\Board\Services\BoardService;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Plugins\Board\Components\UIObjects\Title\TitleUIObject;
use Xpressengine\Plugins\Board\Components\UIObjects\Share\ShareUIObject;
use Xpressengine\Plugins\Board\Components\ToggleMenus\Shares\CopyItem;
use Xpressengine\Plugins\Board\Components\ToggleMenus\Shares\FacebookItem;
use Xpressengine\Plugins\Board\Components\ToggleMenus\Shares\LineItem;
use Xpressengine\Plugins\Board\Components\ToggleMenus\Shares\TwitterItem;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Validator;
use Xpressengine\Plugins\Board\Commands\BoardSkinMake;
use Xpressengine\DynamicField\ColumnEntity;
use Xpressengine\Config\ConfigEntity;
use Schema;
use XeToggleMenu;
use XeConfig;
use XeDB;
use XeInterception;
use XePlugin;
use XeTrash;
use XeCounter;
use XeDynamicField;
use XeDocument;
use XeSkin;
use Illuminate\Console\Application as Artisan;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;

/**
 * Resources
 *
 * Plugin 에서 필요한 리소스 관리
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class Resources
{
    /**
     * create default config
     *
     * @return void
     */
    public static function createDefaultConfig()
    {
        /**
         * @var $configManager ConfigManager
         * @var $dynamicFieldHandler DynamicFieldHandler
         * @var $documentHandler DocumentHandler
         */
        $configManager = app('xe.config');
        $dynamicFieldHandler = app('xe.dynamicField');
        $documentHandler = app('xe.document');
        $configHandler = new ConfigHandler(
            $configManager,
            $dynamicFieldHandler->getConfigHandler(),
            $documentHandler->getConfigHandler()
        );
        $configHandler->getDefault();

        // create default permission
        $permission = new BoardPermissionHandler(app('xe.permission'));
        $permission->addGlobal();

        // create toggle menu
        XeToggleMenu::setActivates('module/board@board', null, [
            'module/board@board/toggleMenu/xpressengine@trashItem',
            'module/board@board/toggleMenu/xpressengine@printItem',
        ]);
    }

    /**
     * create share config
     *
     * @return void
     */
    public static function createShareConfig()
    {
        XeToggleMenu::setActivates(ShareUIObject::CONFIG_NAME, null, [
            CopyItem::getId(),
            FacebookItem::getId(),
            LineItem::getId(),
            TwitterItem::getId(),
        ]);
    }

    /**
     * update languages
     *
     * @return void
     */
    public static function putLang()
    {
        // put board translation source
        /** @var \Xpressengine\Translation\Translator $trans */
        $trans = app('xe.translator');
        $trans->putFromLangDataSource('board', base_path('plugins/board/langs/lang.php'));
    }

    /**
     * bind classes
     *
     * @return void
     */
    public static function bindClasses()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = app();

        $app->singleton(Plugin::class, function ($app) {
            return XePlugin::getPlugin('board');
        });
        $app->alias(Plugin::class, 'xe.plugin.board');

        $app->singleton(Handler::class, function ($app) {
            /** @var Handler $proxyHandler */
            $proxyHandler = XeInterception::proxy(Handler::class);

            /** @var \Xpressengine\Counter\Counter $readCounter */
            $readCounter = XeCounter::make(app('request'), 'read');
            $readCounter->setGuest();
            /** @var \Xpressengine\Counter\Counter $voteCounter */
            $voteCounter = XeCounter::make(app('request'), 'vote', ['assent', 'dissent']);

            $handler = new $proxyHandler(
                app('xe.document'),
                app('xe.storage'),
                app('xe.tag'),
                $readCounter,
                $voteCounter,
                app('xe.plugin.comment')->getHandler()
            );
            return $handler;
        });
        $app->alias(Handler::class, 'xe.board.handler');

        $app->singleton(ConfigHandler::class, function ($app) {

            return new ConfigHandler(
                app('xe.config'),
                XeDynamicField::getConfigHandler(),
                XeDocument::getConfigHandler()
            );
        });
        $app->alias(ConfigHandler::class, 'xe.board.config');

        $app->singleton(UrlHandler::class, function ($app) {
            return new UrlHandler();
        });
        $app->alias(UrlHandler::class, 'xe.board.url');

        $app->singleton(Validator::class, function ($app) {
            return new Validator(app('xe.board.config'), app('xe.dynamicField'));
        });
        $app->alias(Validator::class, 'xe.board.validator');

        $app->singleton(IdentifyManager::class, function ($app) {
            return new IdentifyManager(app('session'), app('xe.document'), app('hash'));
        });
        $app->alias(IdentifyManager::class, 'xe.board.identify');

        $app->singleton(InstanceManager::class, function ($app) {
            return new InstanceManager(
                XeDB::connection(),
                app('xe.document'),
                app('xe.dynamicField'),
                app('xe.board.config'),
                app('xe.board.permission'),
                app('xe.plugin.comment')->getHandler()
            );
        });
        $app->alias(InstanceManager::class, 'xe.board.instance');

        $app->singleton(BoardPermissionHandler::class, function ($app) {
            $boardPermission = new BoardPermissionHandler(app('xe.permission'), app('xe.board.config'));
            $boardPermission->setPrefix(BoardModule::getId());
            return $boardPermission;
        });
        $app->alias(BoardPermissionHandler::class, 'xe.board.permission');

        $app->singleton(BoardService::class, function ($app) {
            $proxyHandler = XeInterception::proxy(BoardService::class);

            $instance = new $proxyHandler(app('xe.board.handler'), app('xe.board.config'));
            return $instance;
        });
        $app->alias(BoardService::class, 'xe.board.service');
    }

    /**
     * register title with slug uiobject
     *
     * @return void
     */
    public static function registerTitleWithSlug()
    {
        /**
         * @var $uiObjectHandler \Xpressengine\UIObject\UIObjectHandler
         */
        $uiObjectHandler = app('xe.uiobject');
        $uiObjectHandler->setAlias('titleWithSlug', TitleUIObject::getId());
        $uiObjectHandler->setAlias('newTitleWithSlug', NewTitleUIObject::getId());
        $uiObjectHandler->setAlias('share', ShareUIObject::getId());
    }

    /**
     * register recycle bin
     *
     * @return void
     */
    public static function registerRecycleBin()
    {
        XeTrash::register(RecycleBin::class);
    }

    /**
     * register commands
     *
     * @return void
     */
    public static function registerCommands()
    {
        $commands = [
            BoardSkinMake::class,
        ];

        Artisan::starting(function ($artisan) use ($commands) {
            $artisan->resolveCommands($commands);
        });
    }

    /**
     * set default skin
     *
     * @return void
     */
    public static function setDefaultSkin()
    {
        XeSkin::setDefaultSkin(
            BoardModule::getId(),
            NewCommonSkin::getId()
        );
    }

    /**
     * register dynamic field create intercept
     *
     * @return void
     */
    public static function interceptDynamicField()
    {
        intercept(
            DynamicFieldHandler::class . '@create',
            'board@commonSkin::createDynamicField',
            function ($func, ConfigEntity $config, ColumnEntity $column = null) {
                $func($config, $column);

                // remove prefix name of group
                $instanceId = str_replace('documents_', '', $config->get('group'));

                /** @var \Xpressengine\Plugins\Board\ConfigHandler $configHandler */
                $configHandler = app('xe.board.config');
                $boardConfig = $configHandler->get($instanceId);
                if ($boardConfig !== null) {
                    $boardConfig->set('formColumns', $configHandler->getSortFormColumns($boardConfig));
                    XeConfig::modify($boardConfig);
                }
            }
        );
    }

    /**
     * register category delete intercept
     *
     * @return void
     */
    public static function interceptDeleteCategory()
    {
        intercept(
            'XeCategory@deleteItem',
            'board::categoryDelete',
            function ($deleteCategory, $item, $force = true) {
                $isUsingCategory = false;

                $delItemUseDocument = BoardCategory::where('item_id', $item->id)->get();

                if (count($delItemUseDocument)) {
                    $isUsingCategory = true;
                }

                if ($isUsingCategory == false && $force == true) {
                    foreach ($item->descendants as $desc) {
                        $descItemUseDocument = BoardCategory::where('item_id', $desc->id)->get();

                        if (count($descItemUseDocument)) {
                            $isUsingCategory = true;
                            break;
                        }
                    }
                }

                if ($isUsingCategory) {
                    throw new AlreadyUseCategoryHttpException;
                }

                $result = $deleteCategory($item, $force);

                return $result;
            }
        );
    }

    /**
     * listen comment retrieved event
     *
     * @return void
     */
    public static function listenCommentRetrievedEvent()
    {
        \Event::listen('xe.plugin.comment.retrieved', function ($request) {
            if (Board::class !== $request->get('target_type')) {
                return;
            }

            /** @var BoardService $boardService */
            $boardService = app('xe.board.service');
            $identifyManager = app('xe.board.identify');
            $boardPermission = app('xe.board.permission');

            $item = Board::find($request->get('target_id'));
            $isManager = Gate::allows(
                BoardPermissionHandler::ACTION_MANAGE,
                new Instance($boardPermission->name($item->getInstanceId()))
            );

            if ($boardService->hasItemPerm($item, auth()->user(), $identifyManager, $isManager) === false &&
                Gate::denies(
                    BoardPermissionHandler::ACTION_READ,
                    new Instance($boardPermission->name($item->getInstanceId()))
                )) {
                throw new AccessDeniedHttpException;
            }
        });
    }

    /**
     * listen comment create event
     *
     * @return void
     */
    public static function listenCommentCreateEvent()
    {
        \Event::listen('xe.plugin.comment.create', function ($request) {
            if (Board::class !== $request->get('target_type')) {
                return;
            }

            $item = Board::find($request->get('target_id'));
            if ($item && !$item->boardData->allow_comment) {
                abort(500, xe_trans('comment::notAllowedComment'));
            }
        });
    }
}
