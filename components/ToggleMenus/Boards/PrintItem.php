<?php
/**
 * PrintItem
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
namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Gate;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\ToggleMenu\AbstractToggleMenu;
use Xpressengine\Permission\Instance;

/**
 * PrintItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class PrintItem extends AbstractToggleMenu
{
    /**
     * get text
     *
     * @return string
     */
    public function getText()
    {
        return xe_trans('board::print');
    }

    /**
     * get type
     *
     * @return string
     */
    public function getType()
    {
        return static::MENUTYPE_RAW;
    }

    /**
     * get action url
     *
     * @return string
     */
    public function getAction()
    {
        $doc = Board::find($this->identifier);

        $url = app('xe.board.url')->get('print', ['id' => $this->identifier], $doc->instance_id);

        return '<a href="'.$url.'" target="_blank">'.$this->getText().'</a>';
    }

    /**
     * get script
     *
     * @return null
     */
    public function getScript()
    {
        return null;
    }
}
