<?php
/**
 * BoardData
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Board\Models;

use Illuminate\Database\Query\JoinClause;
use Xpressengine\Database\Eloquent\Builder;
use Xpressengine\Database\Eloquent\DynamicModel;
use Xpressengine\Http\Request;

/**
 * BoardData
 *
 * @property string target_id
 * @property int allowComment
 * @property int useAlarm
 * @property int fileCount
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class BoardData extends DynamicModel
{
    protected $table = 'board_data';

    public $timestamps = false;

    protected $primaryKey = 'target_id';

    protected $fillable = ['allow_comment', 'use_alarm', 'file_count'];

    public $incrementing = false;

    protected $casts = [
        'allow_comment' => 'int',
        'use_alarm' => 'int',
        'file_count' => 'int',
    ];

    /**
     * check alarm status
     *
     * @return bool
     */
    public function isAlarm()
    {
        return $this->getAttribute('use_alarm') == 1;
    }
}
