<?php
/**
 * Plugin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board;

use Illuminate\Database\Schema\Builder;
use Schema;
use Illuminate\Database\Schema\Blueprint;
use Xpressengine\Plugin\AbstractPlugin;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Config\ConfigManager;
use Xpressengine\DynamicField\DynamicFieldHandler;
use Xpressengine\Permission\PermissionHandler;
use Xpressengine\Counter\Factory as CounterFactory;
use Xpressengine\Plugins\Board\Modules\Board as BoardModule;
use Xpressengine\Plugins\Board\ToggleMenus\TrashItem;
use Xpressengine\Plugins\Board\UIObjects\Share as Share;
use Xpressengine\Plugins\Claim\ToggleMenus\BoardClaimItem;
use XeToggleMenu;
use XeConfig;
use XeDB;
use XePlugin;
use XeTrash;

/**
 * Plugin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
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
        $this->createDefaultConfig();
        $this->createShareConfig();

        $schema = Schema::setConnection(XeDB::connection('document')->master());
        $this->createDataTable($schema);
        $this->createFavoriteTable($schema);
        $this->createSlugTable($schema);
        $this->createCategoryTable($schema);
        $this->createGalleryThumbnailTable($schema);

        $this->putLang();
    }

    protected function createDefaultConfig()
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
        $permission = new BoardPermissionHandler(app('xe.permission'));
        $permission->addGlobal();

        // create toggle menu
        XeToggleMenu::setActivates('module/board@board', null, [
            'module/board@board/toggleMenu/xpressengine@trashItem',
        ]);
    }

    protected function createShareConfig()
    {
        $configManager = app('xe.config');
        $configManager->add(Share::CONFIG_NAME, array_keys(Share::getItems()));
    }

    protected function putLang()
    {
        // put board translation source
        /** @var Translator $trans */
        $trans = app('xe.translator');
        $trans->putFromLangDataSource('board', base_path('plugins/board/langs/lang.php'));
    }

    protected function createDataTable(Builder $schema)
    {
        if ($schema->hasTable('board_data') === false) {
            $schema->create('board_data', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('targetId', 255);

                $table->integer('allowComment')->default(1);
                $table->integer('useAlarm')->default(1);
                $table->integer('fileCount')->default(0);

                $table->primary(array('targetId'));
            });
        }
    }

    protected function createFavoriteTable(Builder $schema)
    {
        if ($schema->hasTable('board_favorites') === false) {
            $schema->create('board_favorites', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->bigIncrements('favoriteId');
                $table->string('targetId', 255);
                $table->string('userId', 255);

                $table->index(array('targetId', 'userId'));
            });
        }
    }

    protected function createSlugTable(Builder $schema)
    {
        if ($schema->hasTable('board_slug') === false) {
            $schema->create('board_slug', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->bigIncrements('id');
                $table->string('targetId', 255);
                $table->string('instanceId', 255);
                $table->string('slug', 255);
                $table->string('title', 255);

                $table->unique(array('slug'));
                $table->index(array('title'));
                $table->index(array('targetId'));
            });
        }
    }

    protected function createCategoryTable(Builder $schema)
    {
        if ($schema->hasTable('board_category') === false) {
            $schema->create('board_category', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('targetId', 255);
                $table->string('itemId', 255);

                $table->primary(array('targetId'));
            });
        }
    }

    protected function createGalleryThumbnailTable(Builder $schema)
    {
        if ($schema->hasTable('board_gallery_thumbs') === false) {
            $schema->create('board_gallery_thumbs', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('targetId', 255);
                $table->string('boardThumbnailFileId', 255);
                $table->string('boardThumbnailExternalPath', 255);
                $table->string('boardThumbnailPath', 255);

                $table->primary(array('targetId'));
            });
        }
    }

    /**
     * @param null $installedVersion install version
     * @return void
     */
    public function update($installedVersion = null)
    {
        $this->putLang();

        // ver 0.9.1
        if (XeConfig::get(XeToggleMenu::getConfigKey('module/board@board', null)) == null) {
            XeToggleMenu::setActivates('module/board@board', null, [
                'module/board@board/toggleMenu/xpressengine@trashItem',
            ]);
        }

        $installedVersion = XePlugin::getPlugin(self::getId())->getInstalledVersion();

        // ver 0.9.2
        if ($installedVersion !== null && $this->hasSlugTableSlugUnique($installedVersion) === false) {
            $schema = Schema::setConnection(XeDB::connection('document')->master());
            $schema->table('board_slug', function(Blueprint $table) {
                $table->dropIndex(array('slug'));
                $table->unique(array('slug'));
            });
        }

        // ver 0.9.5
        if ($this->hasConfigCaptchaTag() === false) {
            $config = XeConfig::get('module/board@board');
            if ($config->get('useCaptcha') === null) {
                $config->set('useCaptcha', false);
            }

            if ($config->get('useTag') === null) {
                $config->set('useTag', true);
            }

            XeConfig::modify($config);
        }
    }

    /**
     * @return boolean
     */
    public function checkUpdated($installedVersion = NULL)
    {
        // ver 0.9.1
        if (XeConfig::get(XeToggleMenu::getConfigKey('module/board@board', null)) == null) {
            return false;
        }

        $installedVersion = XePlugin::getPlugin(self::getId())->getInstalledVersion();

        // ver 0.9.2
        if ($installedVersion !== null && $this->hasSlugTableSlugUnique($installedVersion) === false) {
            return false;
        }

        // ver 0.9.5
        if ($this->hasConfigCaptchaTag() === false) {
            return false;
        }

        return true;
    }


    /**
     * has config for version 0.9.5
     *
     * @return bool
     */
    protected function hasConfigCaptchaTag()
    {
        $config = XeConfig::get('module/board@board');
        if ($config->get('useCaptcha') === null || $config->get('useTag') === null) {
            return false;
        }
        return true;
    }

    /**
     * 0.9.1 이하 버전은 slug를 unique 하게 해야함
     *
     * @return bool
     */
    protected function hasSlugTableSlugUnique($installedVersion)
    {
        if (version_compare($installedVersion, '0.9.1', '<=')) {
            return false;
        }

        return true;
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
        $this->registerRecycleBin();
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

        // Handler
        $app->singleton('xe.board.handler', function ($app) {
            /** @var Handler $proxyHandler */
            $proxyHandler = $app['xe.interception']->proxy(Handler::class);

            $readCounter = app('xe.counter')->make($app['request'], 'read');
            $readCounter->setGuest();
            $voteCounter = app('xe.counter')->make($app['request'], 'vote', ['assent', 'dissent']);

            $handler = new $proxyHandler(
                app('xe.document'),
                app('xe.storage'),
                app('xe.tag'),
                $readCounter,
                $voteCounter
            );
            return $handler;
        });
        $app->bind(
            'Xpressengine\Plugins\Board\Handler',
            'xe.board.handler'
        );

        // ConfigHandler
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

        // UrlHandler
        $app->singleton('xe.board.url', function ($app) {
            return new UrlHandler();
        });
        $app->bind(
            'Xpressengine\Plugins\Board\UrlHandler',
            'xe.board.url'
        );

        // Vlidator
        $app->singleton('xe.board.validator', function ($app) {
            return new Validator($app['xe.board.config'], $app['xe.dynamicField']);
        });
        $app->bind(
            'Xpressengine\Plugins\Board\Validator',
            'xe.board.validator'
        );

        // IdentifyManager
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

        $app->singleton('xe.board.instance', function ($app) {
            /**
             * @var $documentHandler DocumentHandler
             * @var $dynamicFieldHandler DynamicFieldHandler
             */
            $documentHandler = app('xe.document');
            $dynamicFieldHandler = app('xe.dynamicField');

            return new InstanceManager(
                $app['xe.db']->connection('document'),
                $documentHandler,
                $dynamicFieldHandler,
                $app['xe.board.config'],
                $app['xe.board.permission'],
                $app['xe.plugin.comment']->getHandler()
            );
        });
        $app->bind(
            'Xpressengine\Plugins\Board\InstanceManager',
            'xe.board.instance'
        );

        // BoardPermissionHandler
        $app->singleton('xe.board.permission', function ($app) {
            $boardPermission = new BoardPermissionHandler($app['xe.permission'], $app['xe.board.config']);
            $boardPermission->setPrefix(BoardModule::getId());
            return $boardPermission;
        });
        $app->bind(
            'Xpressengine\Plugins\Board\BoardPermissionHandler',
            'xe.board.permission'
        );
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

        $register->add(UIObjects\Title::class);
        $uiObjectHandler->setAlias('titleWithSlug', UIObjects\Title::getId());

        $register->add(UIObjects\Share::class);
        $uiObjectHandler->setAlias('share', UIObjects\Share::getId());
    }

    private function registerRecycleBin()
    {
        XeTrash::register(RecycleBin::class);
    }
}
