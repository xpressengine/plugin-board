<?php
/**
 * Tag
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
namespace Xpressengine\Plugins\Board\Components\UIObjects\Tag;

use Xpressengine\UIObject\AbstractUIObject;
use View;
use XePlugin;

/**
 * Tag
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
class TagUIObject extends AbstractUIObject
{
    /**
     * @var bool
     */
    protected static $loaded = false;

    /**
     * @var string
     */
    protected static $id = 'uiobject/board@tag';

    /**
     * render
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        $args = $this->arguments;

        if (empty($args['tags'])) {
            $args['tags'] = [];
        }

        $args['strTags'] = '';
        if (is_array($args['tags']) && count($args['tags']) > 0) {
            $tagWords = [];
            foreach ($args['tags'] as $tag) {
                $tagWords[] = $tag['word'];
            }
            $args['strTags'] = sprintf('["%s"]', implode('","', $tagWords));
        }


        if (empty($args['id'])) {
            $args['id'] = 'xeBoardTagWrap';
        }
        if (empty($args['titleDomName'])) {
            $args['titleDomName'] = 'Tag';
        }

        if (empty($args['class'])) {
            $args['class'] = 'xe-select-label __xe-board-tag';
        }

        if (empty($args['placeholder'])) {
            $args['placeholder'] = xe_trans('board::inputTag');
        }

        if (empty($args['url'])) {
            $args['url'] = '/editor/hashTag';
        }

        $args['scriptInit'] = false;
        if (self::$loaded === false) {
            self::$loaded = true;

            $args['scriptInit'] = true;
        }

        $plugin = XePlugin::getPlugin('board');
        return View::make('board::components/UIObjects/Tag/tag', $args)->render();
    }
}
