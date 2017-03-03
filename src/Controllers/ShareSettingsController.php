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
use XePresenter;
use XeConfig;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\UIObjects\Share;

class ShareSettingsController extends Controller
{
    public function edit()
    {
        $config = XeConfig::get('share');

        $allItems = Share::getItems();

        $items = [];
        foreach ($config as $key) {
            $items[$key] = $allItems[$key];
            $items[$key]['activated'] = true;
        }

        foreach ($allItems as $key => $item) {
            if (empty($items[$key]) === true) {
                $items[$key] = $item;
                $items[$key]['activated'] = false;
            }
        }

        return XePresenter::make('board::views.share.setting', [
            'items' => $items,
        ]);
    }

    public function update(Request $request)
    {
        $inputs = $request->all();

        $items = [];
        foreach ($inputs['items'] as $key) {
            $items[] = $key;
        }

        XeConfig::put(Share::CONFIG_NAME, $items);

        return redirect()->to(route('manage.board.share.edit'));
    }
}
