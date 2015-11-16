<?php
namespace Xpressengine\Plugins\Board\UIObject;

use Xpressengine\UIObject\AbstractUIObject;
use View;

class Title extends AbstractUIObject
{
    protected static $loaded = false;

    protected static $id = 'uiobject/board@title';

    /**
     * render
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        $args = $this->arguments;

        if (empty($args['id'])) {
            $args['id'] = '';
        }

        if (empty($args['slug'])) {
            $args['slug'] = '';
        }

        if (empty($args['slugDomName'])) {
            $args['slugDomName'] = 'slug';
        }
        if (empty($args['titleDomName'])) {
            $args['titleDomName'] = 'title';
        }

        if (empty($args['titleClassName'])) {
            $args['titleClassName'] = 'form-control title';
        }

        $args['scriptInit'] = false;
        if (self::$loaded === false) {
            self::$loaded = true;

            $args['scriptInit'] = true;
        }

        $plugin = app('xe.plugin.board');
        return View::make(sprintf('%s::views.uiobject.title', $plugin->getId()), $args)->render();
    }

    public static function boot()
    {

    }

}
