<?php
/**
 * Plugin
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
        Resources::registerCommands();
        Resources::setDefaultSkin();
        Resources::interceptDynamicField();
        Resources::interceptDeleteCategory();

        Resources::listenCommentRetrievedEvent();
        Resources::listenCommentCreateEvent();

        Resources::bootGlobalTabMenus();
        Resources::bootInstanceTabMenus();
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
