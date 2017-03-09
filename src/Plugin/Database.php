<?php
/**
 * Database
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

use Schema;
use XeDB;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;

/**
 * Database
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
class Database
{
    /**
     * create database for this plugin
     *
     * @return void
     */
    public static function create()
    {
        $schema = Schema::setConnection(XeDB::connection('document')->master());
        static::createDataTable($schema);
        static::createFavoriteTable($schema);
        static::createSlugTable($schema);
        static::createCategoryTable($schema);
        static::createGalleryThumbnailTable($schema);
    }

    /**
     * create data table
     *
     * @param Builder $schema schema
     * @return void
     */
    protected static function createDataTable(Builder $schema)
    {
        if ($schema->hasTable('board_data') === false) {
            $schema->create('board_data', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('targetId', 36);

                $table->integer('allowComment')->default(1);
                $table->integer('useAlarm')->default(1);
                $table->integer('fileCount')->default(0);

                $table->primary(array('targetId'));
            });
        }
    }

    /**
     * create favorite table
     *
     * @param Builder $schema schema
     * @return void
     */
    protected static function createFavoriteTable(Builder $schema)
    {
        if ($schema->hasTable('board_favorites') === false) {
            $schema->create('board_favorites', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->bigIncrements('favoriteId');
                $table->string('targetId', 36);
                $table->string('userId', 36);

                $table->index(array('targetId', 'userId'));
            });
        }
    }

    /**
     * create slug table
     *
     * @param Builder $schema schema
     * @return void
     */
    protected static function createSlugTable(Builder $schema)
    {
        if ($schema->hasTable('board_slug') === false) {
            $schema->create('board_slug', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->bigIncrements('id');
                $table->string('targetId', 36);
                $table->string('instanceId', 36);
                $table->string('slug', 190);
                $table->string('title', 180);

                $table->unique(array('slug'));
                $table->index(array('title'));
                $table->index(array('targetId'));
            });
        }
    }

    /**
     * create category table
     *
     * @param Builder $schema schema
     * @return void
     */
    protected static function createCategoryTable(Builder $schema)
    {
        if ($schema->hasTable('board_category') === false) {
            $schema->create('board_category', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('targetId', 36);
                $table->integer('itemId');

                $table->primary(array('targetId'));
            });
        }
    }

    /**
     * create gallery thumbnail table
     *
     * @param Builder $schema schema
     * @return void
     */
    protected static function createGalleryThumbnailTable(Builder $schema)
    {
        if ($schema->hasTable('board_gallery_thumbs') === false) {
            $schema->create('board_gallery_thumbs', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('targetId', 36);
                $table->string('boardThumbnailFileId', 255);
                $table->string('boardThumbnailExternalPath', 255);
                $table->string('boardThumbnailPath', 255);

                $table->primary(array('targetId'));
            });
        }
    }
}
