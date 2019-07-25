<?php
/**
* ShareManagerController
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
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
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
