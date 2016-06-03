<?php
/**
 * BoardCategory
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

use Illuminate\Database\Query\JoinClause;
use Xpressengine\Database\Eloquent\Builder;
use Xpressengine\Database\Eloquent\DynamicModel;
use Xpressengine\Http\Request;

/**
 * BoardCategory
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 */
class BoardCategory extends DynamicModel
{
    protected $table = 'board_category';
    public $timestamps = false;

    protected $primaryKey = 'targetId';

    protected $fillable = ['targetId', 'itemId'];

    public function categoryItem()
    {
        return $this->belongsTo('Xpressengine\Category\Models\CategoryItem', 'itemId');
    }
}
