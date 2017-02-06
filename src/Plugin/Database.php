<?php
namespace Xpressengine\Plugins\Board\Plugin;

use Schema;
use XeDB;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;

class Database
{
    static public function create()
    {
        $schema = Schema::setConnection(XeDB::connection('document')->master());
        static::createDataTable($schema);
        static::createFavoriteTable($schema);
        static::createSlugTable($schema);
        static::createCategoryTable($schema);
        static::createGalleryThumbnailTable($schema);
    }

    static protected function createDataTable(Builder $schema)
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

    static protected function createFavoriteTable(Builder $schema)
    {
        if ($schema->hasTable('board_favorites') === false) {
            $schema->create('board_favorites', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->bigIncrements('favoriteId');
                $table->string('targetId', 36)->charset('latin1');
                $table->string('userId', 36)->charset('latin1');

                $table->index(array('targetId', 'userId'));
            });
        }
    }

    static protected function createSlugTable(Builder $schema)
    {
        if ($schema->hasTable('board_slug') === false) {
            $schema->create('board_slug', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->bigIncrements('id');
                $table->string('targetId', 36);
                $table->string('instanceId', 36);
                $table->string('slug', 255);
                $table->string('title', 255);

                $table->unique(array('slug'));
                $table->index(array('title'));
                $table->index(array('targetId'));
            });
        }
    }

    static protected function createCategoryTable(Builder $schema)
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

    static protected function createGalleryThumbnailTable(Builder $schema)
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
