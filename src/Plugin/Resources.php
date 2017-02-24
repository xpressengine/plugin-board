<?php
namespace Xpressengine\Plugins\Board\Plugin;

use Xpressengine\Document\DocumentHandler;
use Xpressengine\Config\ConfigManager;
use Xpressengine\DynamicField\DynamicFieldHandler;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\IdentifyManager;
use Xpressengine\Plugins\Board\InstanceManager;
use Xpressengine\Plugins\Board\Modules\Board as BoardModule;
use Xpressengine\Plugins\Board\Plugin;
use Xpressengine\Plugins\Board\RecycleBin;
use Xpressengine\Plugins\Board\UIObjects\Title;
use Xpressengine\Plugins\Board\UIObjects\Share;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Validator;
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

class Resources
{
    /**
     * create default config
     *
     * @return void
     */
    static public function createDefaultConfig()
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
    static public function createShareConfig()
    {
        $configManager = app('xe.config');
        $configManager->add(Share::CONFIG_NAME, array_keys(Share::getItems()));
    }

    /**
     * update languages
     *
     * @return void
     */
    static public function putLang()
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
    static public function bindClasses()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = app();

        $app->singleton(['xe.plugin.board' => Plugin::class], function ($app) {
            return XePlugin::getPlugin('board');
        });

        $app->singleton(['xe.board.handler' => Handler::class], function ($app) {
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

        $app->singleton(['xe.board.config' => ConfigHandler::class], function ($app) {

            return new ConfigHandler(
                app('xe.config'),
                XeDynamicField::getConfigHandler(),
                XeDocument::getConfigHandler()
            );
        });

        $app->singleton(['xe.board.url' => UrlHandler::class], function ($app) {
            return new UrlHandler();
        });

        $app->singleton(['xe.board.validator' => Validator::class], function ($app) {
            return new Validator(app('xe.board.config'), app('xe.dynamicField'));
        });

        $app->singleton(['xe.board.identify' => IdentifyManager::class], function ($app) {
            return new IdentifyManager(app('session'), app('xe.document'), app('hash'));
        });

        $app->singleton(['xe.board.instance' => InstanceManager::class], function ($app) {
            return new InstanceManager(
                XeDB::connection('document'),
                app('xe.document'),
                app('xe.dynamicField'),
                app('xe.board.config'),
                app('xe.board.permission'),
                app('xe.plugin.comment')->getHandler()
            );
        });

        $app->singleton(['xe.board.permission' => BoardPermissionHandler::class], function ($app) {
            $boardPermission = new BoardPermissionHandler(app('xe.permission'), app('xe.board.config'));
            $boardPermission->setPrefix(BoardModule::getId());
            return $boardPermission;
        });
    }

    /**
     * register title with slug uiobject
     *
     * @return void
     */
    static public function registerTitleWithSlug()
    {
        /**
         * @var $register \Xpressengine\Plugin\PluginRegister
         * @var $uiObjectHandler \Xpressengine\UIObject\UIObjectHandler
         */
        $register = app('xe.pluginRegister');
        $uiObjectHandler = app('xe.uiobject');

        $register->add(Title::class);
        $uiObjectHandler->setAlias('titleWithSlug', Title::getId());

        $register->add(Share::class);
        $uiObjectHandler->setAlias('share', Share::getId());
    }

    /**
     * register recycle bin
     *
     * @return void
     */
    static public function registerRecycleBin()
    {
        XeTrash::register(RecycleBin::class);
    }
}
