<?php
/**
 * BoardGalleryThumb
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Models;

use Xpressengine\Database\Eloquent\DynamicModel;

/**
 * BoardGalleryThumb
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 */
class BoardGalleryThumb extends DynamicModel
{
    protected $connection = 'document';

    public $timestamps = false;

    protected $primaryKey = 'targetId';

    protected $fillable = ['targetId', 'boardThumbnailFileId', 'boardThumbnailExternalPath', 'boardThumbnailPath'];
}
