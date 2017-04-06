<?php
/**
 * Tag
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
namespace Xpressengine\Plugins\Board\UIObjects;

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
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class Tag extends AbstractUIObject
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
        return View::make(sprintf('%s::views.uiobject.tag', $plugin->getId()), $args)->render();
    }
}
