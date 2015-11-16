<?php
/**
 * Plugin
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board;

use Trash;
use Xpressengine\Counter\Counter;
use Xpressengine\Permission\Action;
use Xpressengine\Plugin\AbstractPlugin;
use Xpressengine\Plugins\Board\Addon\AddonManager;
use Xpressengine\Plugins\Board\Controllers\DataImporter;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Comment\CommentHandler;
use Xpressengine\Config\ConfigManager;
use Xpressengine\DynamicField\DynamicFieldHandler;
use Xpressengine\Permission\Factory as PermissionFactory;
use Xpressengine\Member\Repositories\GroupRepositoryInterface as Assignor;
use Xpressengine\Plugins\Board\Counter\ReadCounter;
use Xpressengine\Plugins\Board\Counter\VoteCounter;
use Xpressengine\Plugins\Board\Order\OrderManager;
use Xpressengine\Plugins\ShortIdGenerator\Plugin as ShortIdGenerator;
use Xpressengine\Translation\Translator;

/**
 * Plugin
 *
 * @category    Board
 * @package     Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Plugin extends AbstractPlugin
{
    /**
     * activate
     *
     * @param null $installedVersion installed version
     * @return void
     */
    public function activate($installedVersion = null)
    {
    }

    /**
     * @return void
     */
    public function install()
    {
        // create default config
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
        /**
         * @var $permission PermissionFactory
         * @var $group Assignor
         */
        $permission = app('xe.permission');
        $group = app('xe.member.groups');
        $action = new Action();
        $permission = new PermissionHandler($permission, $group, $action, $configHandler);
        $permission->setDefault($permission->getDefault());

        // create slug database table
        $m = new Migrations\BoardMigration;
        $m->install();

        // put board translation source
        /** @var Translator $trans */
        $trans = app('xe.translator');
        $trans->putFromLangDataSource('board', base_path('plugins/board/langs/lang.php'));

        // set config for counter
        /** @var Counter $counter */
        $counter = app('xe.counter');
        $counter->getConfigHandler()->set(ReadCounter::COUNTER_NAME, Counter::TYPE_SESSION);
        $counter->getConfigHandler()->set(VoteCounter::COUNTER_NAME, Counter::TYPE_ID);
    }

    /**
     * @param null $installedVersion install version
     * @return bool
     */
    public function checkInstall($installedVersion = null)
    {
        if ($installedVersion === null) {
            return false;
        }
    }

    /**
     * @param null $installedVersion install version
     * @return void
     */
    public function update($installedVersion = null)
    {
    }

    /**
     * @return boolean
     */
    public function checkInstalled()
    {
        // TODO: Implement checkInstall() method.

        return true;
    }

    /**
     * @return boolean
     */
    public function checkUpdated()
    {
        // TODO: Implement checkUpdate() method.
    }


    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        $this->bindClasses();
        $this->registerTitleWithSlug();
        $this->registerWaste();
    }

    /**
     * bind classes
     *
     * @return void
     */
    private function bindClasses()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = app();

        $app->singleton('xe.plugin.board', function ($app) {
            return $this;
        });
        $app->bind(
            'Xpressengine\Plugins\Board\Plugin',
            'xe.plugin.board'
        );

        $app->singleton('xe.board.handler', function ($app) {
            /**
             * @var $documentHandler DocumentHandler
             * @var $shortIdGenerator ShortIdGenerator
             */
            $documentHandler = app('xe.document');
            $shortIdGenerator = app('xe.plugin.shortIdGenerator');
            $handler = new Handler(
                $documentHandler,
                $shortIdGenerator,
                app('xe.storage'),
                app('xe.board.slug'),
                app('xe.members'),
                app('xe.auth')
            );
            return $handler;
        });
        $app->bind(
            'Xpressengine\Plugins\Board\Handler',
            'xe.board.handler'
        );

        $app->singleton('xe.board.slug', function ($app) {
            $connector = $app['xe.db']->connection();
            return new SlugRepository($connector);
        });
        $app->bind(
            'Xpressengine\Plugins\Board\SlugRepository',
            'xe.board.slug'
        );

        $app->singleton('xe.board.config', function ($app) {
            /**
             * @var $configManager ConfigManager
             * @var $dynamicFieldHandler DynamicFieldHandler
             * @var $documentHandler DocumentHandler
             */
            $configManager = app('xe.config');
            $dynamicFieldHandler = app('xe.dynamicField');
            $documentHandler = app('xe.document');

            return new ConfigHandler(
                $configManager,
                $dynamicFieldHandler->getConfigHandler(),
                $documentHandler->getConfigHandler()
            );
        });
        $app->bind(
            'Xpressengine\Plugins\Board\ConfigHandler',
            'xe.board.config'
        );

        $app->singleton('xe.board.instance', function ($app) {
            /**
             * @var $documentHandler DocumentHandler
             * @var $dynamicFieldHandler DynamicFieldHandler
             * @var $commentHandler CommentHandler
             */
            $documentHandler = app('xe.document');
            $dynamicFieldHandler = app('xe.dynamicField');
            $commentHandler = app('xe.comment');

            return new InstanceManager(
                $documentHandler,
                $dynamicFieldHandler,
                $commentHandler,
                $app['xe.board.config']
            );
        });
        $app->bind(
            'Xpressengine\Plugins\Board\InstanceManager',
            'xe.board.instance'
        );

        $app->singleton('xe.board.permission', function ($app) {
            /**
             * @var $permission PermissionFactory
             * @var $group Assignor
             * @var $action Action
             */
            $permission = app('xe.permission');
            $group = app('xe.member.groups');
            $action = new Action();

            return new PermissionHandler($permission, $group, $action, $app['xe.board.config']);
        });
        $app->bind(
            'Xpressengine\Plugins\Board\PermissionHandler',
            'xe.board.permission'
        );

        $app->singleton('xe.board.url', function ($app) {
            return new UrlHandler(app('xe.board.slug'));
        });
        $app->bind(
            'Xpressengine\Plugins\Board\UrlHandler',
            'xe.board.url'
        );

        $app->singleton('xe.board.addon', function ($app) {
            return new AddonManager(app('xe.pluginRegister'));
        });
        $app->bind(
            'Xpressengine\Plugins\Board\AddOn\AddonManager',
            'xe.board.addon'
        );

        $app->singleton('xe.board.order', function ($app) {
            return new OrderManager(app('xe.pluginRegister'));
        });
        $app->bind(
            'Xpressengine\Plugins\Board\Order\OrderManager',
            'xe.board.order'
        );

        $app->singleton('xe.board.validator', function ($app) {
            return new Validator($app['xe.board.config'], $app['xe.dynamicField']);
        });
        $app->bind(
            'Xpressengine\Plugins\Board\Validator',
            'xe.board.validator'
        );

        $app->singleton('xe.board.vote', function ($app) {
            /**
             * @var $documentHandler DocumentHandler
             * @var $counter \Xpressengine\Counter\Counter
             */
            $documentHandler = app('xe.document');
            $counter = app('xe.counter');
            return new VoteCounter($documentHandler, $counter);
        });
        $app->bind(
            'Xpressengine\Plugins\Board\Counter\VoteCounter',
            'xe.board.vote'
        );

        $app->singleton('xe.board.readCounter', function ($app) {
            /**
             * @var $documentHandler DocumentHandler
             * @var $counter \Xpressengine\Counter\Counter
             */
            $documentHandler = app('xe.document');
            $counter = app('xe.counter');
            return new ReadCounter($documentHandler, $counter);
        });
        $app->bind(
            'Xpressengine\Plugins\Board\Counter\ReadCounter',
            'xe.board.readCounter'
        );

        $app->singleton('xe.board.identify', function ($app) {
            /**
             * @var $session \Illuminate\Session\SessionManager
             * @var $documentHandler DocumentHandler
             */
            $session = app('session');
            $documentHandler = app('xe.document');

            return new IdentifyManager($session, $documentHandler, app('hash'));
        });
        $app->bind(
            'Xpressengine\Plugins\Board\IdentifyManager',
            'xe.board.identify'
        );

        $app->singleton('xe.board.revision', function ($app) {
            /** @var DocumentHandler $documentHandler */
            $documentHandler = app('xe.document');

            return new RevisionHandler($documentHandler, app('xe.board.config'));
        });
        $app->bind(
            'Xpressengine\Plugins\Board\RevisionHandler',
            'xe.board.revision'
        );

        $app->singleton('xe.board.dataImporter', function ($app) {
            return new DataImporter();
        });
        $app->bind(
            'Xpressengine\Plugins\Board\Controllers\DataImporter',
            'xe.board.dataImporter'
        );
    }

    /**
     * register waste
     *
     * @return void
     */
    private function registerWaste()
    {
        Trash::register(Waste\Waste::class);
    }

    /**
     * register title with slug uiobject
     *
     * @return void
     */
    private function registerTitleWithSlug()
    {
        /**
         * @var $register \Xpressengine\Plugin\PluginRegister
         * @var $uiObjectHandler \Xpressengine\UIObject\UIObjectHandler
         */
        $register = app('xe.pluginRegister');
        $uiObjectHandler = app('xe.uiobject');

        $register->add(UIObject\Title::class);
        $uiObjectHandler->setAlias('titleWithSlug', UIObject\Title::getId());
    }
}
