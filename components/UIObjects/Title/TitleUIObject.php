<?php
/**
 * Title
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
namespace Xpressengine\Plugins\Board\Components\UIObjects\Title;

use Xpressengine\UIObject\AbstractUIObject;
use View;
use XePlugin;

/**
 * Title
 *
 * 게시판 글 등록할 때 slug 지원하는 input box
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class TitleUIObject extends AbstractUIObject
{
    /**
     * @var bool
     */
    protected static $loaded = false;

    /**
     * @var string
     */
    protected static $id = 'uiobject/board@title';

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

        $args['scriptInit'] = false;
        if (self::$loaded === false) {
            self::$loaded = true;

            $args['scriptInit'] = true;
        }

        $plugin = XePlugin::getPlugin('board');
        return View::make('board::components/UIObjects/Title/title', $args)->render();
    }
}
