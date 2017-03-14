<?php
/**
 * BoardCategory
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
namespace Xpressengine\Plugins\Board\Models;

use Xpressengine\Database\Eloquent\DynamicModel;
use Xpressengine\Category\Models\CategoryItem;

/**
 * BoardCategory
 *
 * @property string targetId
 * @property int itemId
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class BoardCategory extends DynamicModel
{
    protected $table = 'board_category';

    protected $connection = 'document';

    public $timestamps = false;

    protected $primaryKey = 'targetId';

    protected $fillable = ['targetId', 'itemId'];

    /**
     * get category item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoryItem()
    {
        return $this->belongsTo(CategoryItem::class, 'itemId');
    }

    /**
     * get category item word
     *
     * @return mixed
     */
    public function getWord()
    {
        return $this->categoryItem->word;
    }
}
