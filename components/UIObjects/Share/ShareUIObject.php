<?php
/**
 * Share
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
namespace Xpressengine\Plugins\Board\Components\UIObjects\Share;

use App\Facades\XeFrontend;
use Xpressengine\UIObject\AbstractUIObject;
use View;
use Route;
use XeConfig;

/**
 * Share
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class ShareUIObject extends AbstractUIObject
{
    protected static $loaded = false;

    protected static $id = 'uiobject/board@share';

    /**
     *
     */
    const CONFIG_NAME = 'uiobject/board@share';

    /**
     * boot
     *
     * @return void
     */
    public static function boot()
    {
        self::registerSettingsRoute();
    }

    /**
     * Register Plugin Manage Route
     *
     * @return void
     */
    protected static function registerSettingsRoute()
    {
        Route::settings(self::getId(), function () {
            Route::get(
                '/config',
                ['as' => 'settings.board.share.config', 'uses' => 'ShareSettingsController@config']
            );
        }, ['namespace' => 'Xpressengine\Plugins\Board\Controllers']);
    }

    /**
     * render
     *
     * @return mixed
     */
    public function render()
    {
        if (self::$loaded === false) {
            self::$loaded = true;
            XeFrontend::js('/plugins/board/components/UIObjects/Share/assets/share.js')->load();
        }

        $args = $this->arguments;

        $item = $args['item'];
        $url = $args['url'];
        $className = (isset($args['className'])) ? $args['className'] : '';


        return View::make('board::components/UIObjects/Share/share', [
            'url' => $url,
            'item' => $item,
            'className' => $className
        ])->render();
    }

    /**
     * get manage URI
     *
     * @return string
     */
    public static function getSettingsURI()
    {
        return route('settings.board.share.config');
    }
}
