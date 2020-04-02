<?php
namespace Xpressengine\Plugins\Board\Components\Widgets\ArticleList\Skins\Basic;

use Xpressengine\Skin\GenericSkin;
use View;

class BasicListSkin extends GenericSkin
{
    /**
     * @var string
     */
    protected static $path = 'board/components/Widgets/ArticleList/Skins/Basic';

    public static function view($view)
    {
        $dir = static::$viewDir ? '.' . static::$viewDir : '';
        $view = str_replace('/', '.', static::$path) . "$dir.widget-list";
        return $view;
    }

    public static function info($key = null, $default = null)
    {
        if (static::$info === null) {
            static::$info = include(base_path(static::getPath().'/'.'info-list.php'));
        }

        if ($key !== null) {
            return array_get(static::$info, $key, $default);
        }
        return static::$info;
    }
}
