<?php
/**
 * Database
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

use Schema;
use XeDB;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;

/**
 * Database
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
class Database
{
    /**
     * create database for this plugin
     *
     * @return void
     */
    public static function create()
    {
        static::createDataTable();
        static::createFavoriteTable();
        static::createSlugTable();
        static::createCategoryTable();
        static::createGalleryThumbnailTable();
    }

    /**
     * create data table
     *
     * @return void
     */
    protected static function createDataTable()
    {
        if (Schema::hasTable('board_data') === false) {
            Schema::create('board_data', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('target_id', 36);

                $table->integer('allow_comment')->default(1);
                $table->integer('use_alarm')->default(1);
                $table->integer('file_count')->default(0);

                $table->primary(array('target_id'));
            });
        }
    }

    /**
     * create favorite table
     *
     * @return void
     */
    protected static function createFavoriteTable()
    {
        if (Schema::hasTable('board_favorites') === false) {
            Schema::create('board_favorites', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->bigIncrements('favorite_id');
                $table->string('target_id', 36);
                $table->string('user_id', 36);

                $table->index(array('target_id', 'user_id'));
            });
        }
    }

    /**
     * create slug table
     *
     * @return void
     */
    protected static function createSlugTable()
    {
        if (Schema::hasTable('board_slug') === false) {
            Schema::create('board_slug', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->bigIncrements('id');
                $table->string('target_id', 36);
                $table->string('instance_id', 36);
                $table->string('slug', 190);
                $table->string('title', 180);

                $table->unique(array('slug'));
                $table->index(array('title'));
                $table->index(array('target_id'));
            });
        }
    }

    /**
     * create category table
     *
     * @return void
     */
    protected static function createCategoryTable()
    {
        if (Schema::hasTable('board_category') === false) {
            Schema::create('board_category', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('target_id', 36);
                $table->integer('item_id');

                $table->primary(array('target_id'));
            });
        }
    }

    /**
     * create gallery thumbnail table
     *
     * @return void
     */
    protected static function createGalleryThumbnailTable()
    {
        if (Schema::hasTable('board_gallery_thumbs') === false) {
            Schema::create('board_gallery_thumbs', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('target_id', 36);
                $table->string('board_thumbnail_file_id', 255);
                $table->string('board_thumbnail_external_path', 255);
                $table->string('board_thumbnail_path', 255);

                $table->primary(array('target_id'));
            });
        }
    }
}
