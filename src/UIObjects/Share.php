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
namespace Xpressengine\Plugins\Board\UIObjects;

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
class Share extends AbstractUIObject
{
    /**
     *
     */
    const CONFIG_NAME = 'share';

    /**
     * @var string
     */
    protected static $id = 'uiobject/board@share';

    /**
     * boot
     *
     * @return void
     */
    public static function boot()
    {
        self::registerManageRoute();
    }

    /**
     * Register Plugin Manage Route
     *
     * @return void
     */
    protected static function registerManageRoute()
    {
        Route::settings(self::getId(), function () {
            Route::get(
                '/edit',
                ['as' => 'manage.board.share.edit', 'uses' => 'ShareSettingsController@edit']
            );
            Route::post(
                '/update',
                ['as' => 'manage.board.share.update', 'uses' => 'ShareSettingsController@update']
            );
        }, ['namespace' => 'Xpressengine\Plugins\Board\Controllers']);
    }

    /**
     * get share items
     *
     * @return array
     */
    public static function getItems()
    {
        return [
            'facebook' => [
                'label' => 'board::facebook',
                'url' => 'http://www.facebook.com/sharer/sharer.php?u=__url__',
                'icon' => 'xi-facebook'
            ],
            'twitter' => [
                'label' => 'board::twitter',
                'url' => 'https://twitter.com/intent/tweet?url=__url__',
                'icon' => 'xi-twitter'
            ],
            'line' => [
                'label' => 'board::line',
                'url' => 'http://line.me/R/msg/text/?title=__url__',
                'icon' => 'xi-line'
            ],
        ];
    }

    /**
     * get activated items
     *
     * @return array
     */
    public function getActivated()
    {
        $config = XeConfig::get('share');
        $allItems = static::getItems();
        $items = [];
        foreach ($config as $key) {
            $items[] = $allItems[$key];
        }

        return $items;
    }

    /**
     * render
     *
     * @return mixed
     */
    public function render()
    {
        $args = $this->arguments;

        $url = $args['url'];
        $items = $this->getActivated();
        foreach ($items as $key => $item) {
            $item['url'] = str_replace('__url__', $url, $item['url']);
            $items[$key] = $item;
        }

        return View::make('board::views.uiobject.share', [
            'url' => $url,
            'items' => $items,
        ])->render();
    }

    /**
     * get manage URI
     *
     * @return string
     */
    public static function getSettingsURI()
    {
        return route('manage.board.share.edit');
    }
}
