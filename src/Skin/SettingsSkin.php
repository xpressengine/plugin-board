<?php
/**
 * Board manager skin
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Skin;

use Xpressengine\Skin\AbstractSkin;
use View;

/**
 * Board manager skin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
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
