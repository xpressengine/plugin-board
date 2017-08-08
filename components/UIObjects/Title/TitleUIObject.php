<?php
/**
 * Title
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
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
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
