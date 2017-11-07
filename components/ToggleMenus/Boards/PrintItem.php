<?php
/**
 * PrintItem
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
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
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
        return static::MENUTYPE_LINK;
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

        return $url;
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
