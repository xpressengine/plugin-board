<?php
/**
* ShareManagerController
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

namespace Xpressengine\Plugins\Board\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Sections\ToggleMenuSection;
use XePresenter;
use XeConfig;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\Components\UIObjects\Share\ShareUIObject;

/**
 * ShareSettingsController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class ShareSettingsController extends Controller
{
    /**
     * config
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function config()
    {
        $toggleMenuSection = new ToggleMenuSection(ShareUIObject::getId());

        return XePresenter::make('board::components/UIObjects/Share/setting', [
            'toggleMenuSection' => $toggleMenuSection,
        ]);
    }
}
