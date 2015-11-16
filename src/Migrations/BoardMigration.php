<?php
/**
 * BoardMigration
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Schema;
use Xpressengine\Support\Migration;

/**
 * BoardMigration
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class BoardMigration implements Migration
{
    /**
     * install
     *
     * @return void
     */
    public function install()
    {
        if (Schema::hasTable('slug') === false) {
            Schema::create('slug', function (Blueprint $table) {
                $table->string('slug', 255);
                $table->string('id', 255);
                $table->string('instanceId', 255);
                $table->string('title', 255);

                $table->primary(array('slug'));
                $table->index(array('title'));
            });
        }
    }

    /**
     * update
     *
     * @param string $currentVersion version
     * @return void
     */
    public function update($currentVersion)
    {

    }

    /**
     * check install
     *
     * @return void
     */
    public function checkInstall()
    {
    }

    /**
     * check update
     *
     * @param string $currentVersion version
     * @return void
     */
    public function checkUpdate($currentVersion)
    {
    }
}
