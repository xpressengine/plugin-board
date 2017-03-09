<?php
/**
 * BoardData
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Models;

use Illuminate\Database\Query\JoinClause;
use Xpressengine\Database\Eloquent\Builder;
use Xpressengine\Database\Eloquent\DynamicModel;
use Xpressengine\Http\Request;

/**
 * BoardData
 *
 * @property int favoriteId
 * @property string targetId
 * @property string userId
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class BoardFavorite extends DynamicModel
{
    protected $connection = 'document';

    public $timestamps = false;

    protected $primaryKey = 'favoriteId';

    protected $fillable = ['targetId', 'userId'];
}
