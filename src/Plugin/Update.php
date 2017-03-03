<?php
namespace Xpressengine\Plugins\Board\Plugin;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use XeToggleMenu;
use XeConfig;
use XeDB;
use XePlugin;

class Update
{
    static public function check($installedVersion = null)
    {
        // ver 0.9.1
        if (XeConfig::get(XeToggleMenu::getConfigKey('module/board@board', null)) == null) {
            return false;
        }

        $installedVersion = XePlugin::getPlugin('board')->getInstalledVersion();

        // ver 0.9.2
        if ($installedVersion !== null && static::hasSlugTableSlugUnique($installedVersion) === false) {
            return false;
        }

        // ver 0.9.5
        if (static::hasConfigCaptchaTag() === false) {
            return false;
        }

        // ver 0.9.14
        if (static::hasConfigUrlType() === false) {
            return false;
        }

        // ver 0.9.14
        if (static::hasConfigDeleteToTrash() === false) {
            return false;
        }

        return true;
    }

    /**
     * @param null $installedVersion install version
     * @return void
     */
    static public function proc($installedVersion = null)
    {
        Resources::putLang();

        // ver 0.9.1
        if (XeConfig::get(XeToggleMenu::getConfigKey('module/board@board', null)) == null) {
            XeToggleMenu::setActivates('module/board@board', null, [
                'module/board@board/toggleMenu/xpressengine@trashItem',
            ]);
        }

        $installedVersion = XePlugin::getPlugin('board')->getInstalledVersion();

        // ver 0.9.2
        if ($installedVersion !== null && static::hasSlugTableSlugUnique($installedVersion) === false) {
            $schema = Schema::setConnection(XeDB::connection('document')->master());
            $schema->table('board_slug', function(Blueprint $table) {
                $table->dropIndex(array('slug'));
                $table->unique(array('slug'));
            });
        }

        // ver 0.9.5
        if (static::hasConfigCaptchaTag() === false) {
            $config = XeConfig::get('module/board@board');
            if ($config->get('useCaptcha') === null) {
                $config->set('useCaptcha', false);
            }

            if ($config->get('useTag') === null) {
                $config->set('useTag', true);
            }

            XeConfig::modify($config);
        }

        // ver 0.9.14
        if (static::hasConfigUrlType() === false) {
            $config = XeConfig::get('module/board@board');
            if ($config->get('urlType') === null) {
                $config->set('urlType', 'slug');
            }

            XeConfig::modify($config);
        }

        // ver 0.9.14
        if (static::hasConfigDeleteToTrash() === false) {
            $config = XeConfig::get('module/board@board');
            if ($config->get('deleteToTrash') === null) {
                $config->set('deleteToTrash', false);
            }

            XeConfig::modify($config);
        }
    }

    /**
     * has config for version 0.9.5
     *
     * @return bool
     */
    static protected function hasConfigCaptchaTag()
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
    static protected function hasSlugTableSlugUnique($installedVersion)
    {
        if (version_compare($installedVersion, '0.9.1', '<=')) {
            return false;
        }

        return true;
    }

    static protected function hasConfigUrlType()
    {
        $config = XeConfig::get('module/board@board');
        if ($config->get('urlType') === null) {
            return false;
        }
        return true;
    }

    static protected function hasConfigDeleteToTrash()
    {
        $config = XeConfig::get('module/board@board');
        if ($config->get('deleteToTrash') === null) {
            return false;
        }
        return true;
    }
}
