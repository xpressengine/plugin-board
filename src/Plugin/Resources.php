<?php
/**
 * Resources
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
namespace Xpressengine\Plugins\Board\Plugin;

use Xpressengine\Document\DocumentHandler;
use Xpressengine\Config\ConfigManager;
use Xpressengine\DynamicField\DynamicFieldHandler;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\IdentifyManager;
use Xpressengine\Plugins\Board\InstanceManager;
use Xpressengine\Plugins\Board\Plugin;
use Xpressengine\Plugins\Board\RecycleBin;
use Xpressengine\Plugins\Board\Services\BoardService;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Plugins\Board\Components\Skins\Board\Common\CommonSkin;
use Xpressengine\Plugins\Board\Components\UIObjects\Title\TitleUIObject;
use Xpressengine\Plugins\Board\Components\UIObjects\Share\ShareUIObject;
use Xpressengine\Plugins\Board\Components\ToggleMenus\Shares\CopyItem;
use Xpressengine\Plugins\Board\Components\ToggleMenus\Shares\FacebookItem;
use Xpressengine\Plugins\Board\Components\ToggleMenus\Shares\LineItem;
use Xpressengine\Plugins\Board\Components\ToggleMenus\Shares\TwitterItem;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Validator;
use Xpressengine\Plugins\Board\Commands\BoardSkinMake;
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

/**
 * Resources
 *
 * Plugin 에서 필요한 리소스 관리
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
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
                XeDB::connection('document'),
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

    public static function registerCommands()
    {
        $events = app('events');

        $commands = [
            BoardSkinMake::class,
        ];

        $events->listen('artisan.start', function ($artisan) use ($commands) {
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
            CommonSkin::getId()
        );
    }
}
