<?php
/**
 * BlogSkin
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Skins;

use Xpressengine\Plugins\Board\Skins\DynamicField\DesignSelectSkin;
use Xpressengine\Plugins\Board\Skins\PaginationMobilePresenter;
use Xpressengine\Plugins\Board\Skins\PaginationPresenter;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\Skin\AbstractSkin;
use View;

/**
 * BlogSkin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class BlogSkin extends GallerySkin
{
    protected static $skinAlias = 'board::views.blogSkin';

    public static function boot()
    {
        self::addThumbSkin(static::getId());
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
