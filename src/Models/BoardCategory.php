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
 * @property string target_id
 * @property int item_id
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

    public $timestamps = false;

    protected $primaryKey = 'target_id';

    protected $fillable = ['target_id', 'item_id'];

    /**
     * get category item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoryItem()
    {
        return $this->belongsTo(CategoryItem::class, 'item_id');
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

    /**
     * get array
     *
     * @return array
     */
    public function toArray()
    {
        $this->categoryItem;
        $this->categoryItem->trans_word = xe_trans($this->categoryItem->word);
        $this->categoryItem->trans_description = xe_trans($this->categoryItem->description);

        return parent::toArray();
    }
}
