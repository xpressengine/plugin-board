<?php
/**
 * NewTitle
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2020 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Components\UIObjects\NewTitle;

use View;
use XeFrontend;
use Xpressengine\UIObject\AbstractUIObject;

/**
 * NewTitle
 *
 * 신규 게시판 스킨에서 게시판 글 등록할 때 slug 지원하는 input box
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2020 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class NewTitleUIObject extends AbstractUIObject
{
    /**
     * @var bool
     */
    protected static $loaded = false;

    /**
     * @var string
     */
    protected static $id = 'uiobject/board@new_title';

    /**
     * render
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        $args = $this->arguments;

        if (empty($args['id'])) {
            $args['id'] = '';
        }

        if (empty($args['slug'])) {
            $args['slug'] = '';
        }

        if (empty($args['slugDomName'])) {
            $args['slugDomName'] = 'slug';
        }
        if (empty($args['titleDomName'])) {
            $args['titleDomName'] = 'title';
        }

        if (empty($args['titleClassName'])) {
            $args['titleClassName'] = 'xe-form-control title';
        }

        if (empty($args['titlePlaceholder'])) {
            $args['titlePlaceholder'] = xe_trans('board::enterTitle');
        }

        $args['scriptInit'] = false;
        if (self::$loaded === false) {
            self::$loaded = true;
            $args['scriptInit'] = true;
        }

        return View::make('board::components/UIObjects/NewTitle/title', $args)->render();
    }
}
