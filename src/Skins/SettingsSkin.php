<?php
/**
 * SettingsSkin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Skins;

use Xpressengine\Skin\AbstractSkin;
use View;

/**
 * SettingsSkin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 */
class SettingsSkin extends AbstractSkin
{
    /**
     * render
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $view = View::make(sprintf('board::views.defaultSettings.%s', $this->view), $this->data);

        return $view;
    }

    /**
     * get manage URI
     *
     * @return string
     */
    public static function getSettingsURI()
    {
    }
}
