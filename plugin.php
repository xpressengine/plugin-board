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

use Xpressengine\Plugin\AbstractPlugin;
use Xpressengine\Plugins\Board\Plugin\Database;
use Xpressengine\Plugins\Board\Plugin\Resources;
use Xpressengine\Plugins\Board\Plugin\Update;

/**
 * Plugin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 */
class Plugin extends AbstractPlugin
{
    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        Resources::bindClasses();
        Resources::registerTitleWithSlug();
        Resources::registerRecycleBin();
    }

    /**
     * activate
     *
     * @param null $installedVersion installed version
     * @return void
     */
    public function activate($installedVersion = null)
    {
    }

    /**install
     *
     * @return void
     */
    public function install()
    {
        Database::create();

        Resources::createDefaultConfig();
        Resources::createShareConfig();
        Resources::putLang();
    }

    /**
     * update
     *
     * @param null $installedVersion install version
     * @return void
     */
    public function update($installedVersion = null)
    {
        Update::proc($installedVersion);
    }

    /**
     * check update
     *
     * @param null $installedVersion
     * @return bool
     */
    public function checkUpdated($installedVersion = NULL)
    {
        return Update::check($installedVersion);
    }
}
