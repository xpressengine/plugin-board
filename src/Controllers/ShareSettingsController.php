<?php
/**
* ShareManagerController
*
* @category    Board
* @package     Xpressengine\Plugins\Board
* @author      XE Developers <developers@xpressengine.com>
* @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
* @license     LGPL-2.1
* @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
* @link        https://xpressengine.io
*/

namespace Xpressengine\Plugins\Board\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Sections\ToggleMenuSection;
use XePresenter;
use XeConfig;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\UIObjects\Share;

class ShareSettingsController extends Controller
{
    public function config()
    {
        $toggleMenuSection = new ToggleMenuSection(Share::getId());

        return XePresenter::make('board::views.share.setting', [
            'toggleMenuSection' => $toggleMenuSection,
        ]);
    }
}
