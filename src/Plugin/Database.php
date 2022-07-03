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

use Illuminate\Database\Schema\Blueprint;
use Schema;
use XeDB;

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
     * created database for this plugin
     *
     * @return void
     */
    public static function created()
    {
        static::createdDataTable();
        static::createdFavoriteTable();
        static::createdSlugTable();
        static::createdCategoryTable();
        static::createdGalleryThumbnailTable();
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
                // board data
                $table->engine = 'InnoDB';

                // columns
                $table->string('target_id', 36)->comment("ID of the board's post");

                $table->boolean('allow_comment')
                    ->default(true)
                    ->comment('allow_comment status. true:allow, false:disallow');

                $table->boolean('use_alarm')
                    ->default(true)
                    ->comment('use_alarm status. true:use, false:not used');

                $table->integer('file_count')
                    ->default(0)
                    ->comment('number of attached files');

                $table->string('title_head', 255)
                    ->default('')
                    ->comment('title head is specific tag of title');

                // indexes
                $table->primary(['target_id']);
            });
        }
    }

    /**
     * Created Data Table
     *
     * @return void
     */
    protected static function createdDataTable()
    {
        Schema::table('board_data', function (Blueprint $table) {
            // foreign
            $table->foreign('target_id')->references('id')->on('documents');
        });
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
                // board favorites
                $table->engine = 'InnoDB';

                // columns
                $table->bigIncrements('favorite_id')->comment('ID');
                $table->string('user_id', 36)->comment('ID of the user who liked it');
                $table->string('target_id', 36)->comment("ID of the liked board's post");

                // indexes
                $table->index('user_id');
                $table->unique(['target_id', 'user_id']);
            });
        }
    }

    /**
     * Created favorite Table
     *
     * @return void
     */
    protected static function createdFavoriteTable()
    {
        Schema::table('board_favorites', function (Blueprint $table) {
            // foreign
            $table->foreign('user_id')->references('id')->on('user');
            $table->foreign('target_id')->references('id')->on('documents');
        });
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
                // slug table
                $table->engine = 'InnoDB';

                // columns
                $table->bigIncrements('id')->comment('ID');
                $table->string('target_id', 36)->comment("ID of board's post");
                $table->string('instance_id', 36)->comment('ID of board');
                $table->string('slug', 190)->comment('text for retrieval.');
                $table->string('title', 180)->comment("slug's title");

                // index
                $table->unique(['slug']);
                $table->index(['title']);
                $table->index(['target_id']);
            });
        }
    }

    /**
     * created slug table
     *
     * @return void
     */
    protected static function createdSlugTable()
    {
        Schema::table('board_slug', function (Blueprint $table) {
            // foreign
            $table->foreign('target_id')->references('id')->on('documents');
        });
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
                // board_category_table
                $table->engine = 'InnoDB';

                // columns
                $table->string('target_id', 36)->comment("ID of board's document");
                $table->unsignedInteger('item_id')->comment('ID of category item');

                // foreign
                $table->primary(['target_id']);
                $table->index(['item_id']);
            });
        }
    }

    /**
     * created category table
     *
     * @return void
     */
    protected static function createdCategoryTable()
    {
        Schema::table('board_category', function (Blueprint $table) {
            // foreign
            $table->foreign('target_id')->references('id')->on('documents');
            $table->foreign('item_id')->references('id')->on('category_item');
        });
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
                // board_gallery_thumbs table
                $table->engine = 'InnoDB';

                // columns
                $table->string('target_id', 36)
                    ->comment("ID of board's document");

                $table->string('board_thumbnail_file_id', 255)
                    ->comment("ID of board's thumbnail file");

                $table->string('board_thumbnail_external_path', 255)
                    ->comment("External Path of board's thumbnail file");

                $table->string('board_thumbnail_path', 255)
                    ->comment("Path of board's thumbnail file");

                // index
                $table->primary(['target_id']);
                $table->index('board_thumbnail_file_id');
            });
        }
    }

    /**
     * created category table
     *
     * @return void
     */
    protected static function createdGalleryThumbnailTable()
    {
        Schema::table('board_gallery_thumbs', function (Blueprint $table) {
            // foreign
            $table->foreign('target_id')->references('id')->on('documents');
            $table->foreign('board_thumbnail_file_id')->references('id')->on('files');
        });
    }
}
