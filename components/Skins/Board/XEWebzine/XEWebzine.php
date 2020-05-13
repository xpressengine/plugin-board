<?php
/**
 * XEWebzine
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

namespace Xpressengine\Plugins\Board\Components\Skins\Board\XEWebzine;

use Xpressengine\Plugins\Board\Components\Skins\Board\XEGallery\XEGallery;

/**
 * XEWebzine
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2020 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class XEWebzine extends XEGallery
{
    protected static $path = 'board/components/Skins/Board/XEWebzine';
    
    public function resolveSetting(array $inputs = [])
    {
        if (isset($inputs['visibleIndexBlogProfileImage']) === false) {
            $inputs['visibleIndexBlogProfileImage'] = '';
        }

        if (isset($inputs['visibleIndexBlogDescription']) === false) {
            $inputs['visibleIndexBlogDescription'] = '';
        }
        
        return parent::resolveSetting($inputs);
    }
}
