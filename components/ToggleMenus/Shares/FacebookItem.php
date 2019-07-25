<?php
/**
 * FacebookItem
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

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Shares;

use Xpressengine\ToggleMenu\AbstractToggleMenu;

/**
 * FacebookItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class FacebookItem extends AbstractToggleMenu
{

    /**
     * 메뉴에서 보여질 문자열
     *
     * @return string
     */
    public function getText()
    {
        return xe_trans('board::facebook');
    }

    /**
     * 메뉴의 타입
     * 'exec' or 'link' or 'raw' 중에 하나
     *
     * @return string
     */
    public function getType()
    {
        return static::MENUTYPE_RAW;
    }

    /**
     * 실행되기 위한 js 문자열
     * 타입이 'raw' 인 경우에는 html
     *
     * @return string
     */
    public function getAction()
    {
        $url = 'http://www.facebook.com/sharer/sharer.php?u=' . urlencode(app('request')->get('url'));
        return '<a href="#" class="share-item" data-url="'.$url.'" data-type="facebook"><i class="xi-facebook"></i>'
        .$this->getText().'</a>';
    }

    /**
     * 별도의 js 파일을 load 해야 하는 경우 해당 파일의 경로
     * 없는 경우 null 반환
     *
     * @return string|null
     */
    public function getScript()
    {
        return null;
    }
}
