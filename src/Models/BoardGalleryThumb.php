<?php
/**
 * BoardGalleryThumb
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
namespace Xpressengine\Plugins\Board\Models;

use Xpressengine\Database\Eloquent\DynamicModel;
use Xpressengine\Media\Models\Image;
use Xpressengine\Media\Models\Media;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;

/**
 * BoardGalleryThumb
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class BoardGalleryThumb extends DynamicModel
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'target_id';

    protected $fillable = [
        'target_id',
        'board_thumbnail_file_id',
        'board_thumbnail_external_path',
        'board_thumbnail_path'
    ];

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * thumbnail의 실제 url을 반환
     *
     * @param string $value board_thumbnail_path attribute
     *
     * @return string
     */
    public function getBoardThumbnailPathAttribute($value)
    {
        $thumbnailImage =  Image::find($this->board_thumbnail_file_id);
        if ($thumbnailImage == null) {
            return '';
        }

        if ($value !== '') {
            $media = \XeMedia::getHandler(Media::TYPE_IMAGE)->getThumbnail(
                $thumbnailImage,
                BoardModule::THUMBNAIL_TYPE,
                'L'
            );

            $value = $media->url();
        }

        return $value;
    }
}
