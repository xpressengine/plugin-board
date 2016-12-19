<?php
namespace Xpressengine\Plugins\Board\UIObjects;

use Xpressengine\UIObject\AbstractUIObject;
use View;
use Route;
use XeConfig;

class Share extends AbstractUIObject
{

    const CONFIG_NAME = 'share';

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
                ['as' => 'manage.board.share.edit', 'uses' => 'ShareManagerController@edit']
            );
            Route::post(
                '/update',
                ['as' => 'manage.board.share.update', 'uses' => 'ShareManagerController@update']
            );
        }, ['namespace' => 'Xpressengine\Plugins\Board\Controllers']);
    }

    public static function getItems()
    {
        return [
            'facebook' => ['label' => 'board::facebook', 'url' => 'http://www.facebook.com/sharer/sharer.php?u=__url__', 'icon' => 'xi-facebook'],
            'twitter' => ['label' => 'board::twitter', 'url' => 'https://twitter.com/intent/tweet?url=__url__', 'icon' => 'xi-twitter'],
            'line' => ['label' => 'board::line', 'url' => 'http://line.me/R/msg/text/?title=__url__', 'icon' => 'xi-line'],
        ];
    }

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
