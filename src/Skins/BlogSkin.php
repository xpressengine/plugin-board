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
class BlogSkin extends DefaultSkin
{
    public static function boot()
    {
        GallerySkin::addThumbSkin(static::getId());
    }

    /**
     * render
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // call customizer
        // view 아이디를 기준으로 Customizer 호출
        $customizer = $this->view . 'Customizer';
        if (method_exists($this, $customizer)) {
            $this->$customizer();
        }

        // 리스팅을 제외한 모든 디자인은 기본 스킨의 디자인 사용
        $view = View::make('board::views.defaultSkin._frame', $this->data);
        if ($this->view === 'index') {

            GallerySkin::attachThumbnail($this->data['paginate']);

            $view->content = View::make(
                sprintf('board::views.blogSkin.%s', $this->view),
                $this->data
            )->render();
        } else {
            $view->content = View::make(
                sprintf('board::views.defaultSkin.%s', $this->view),
                $this->data
            )->render();
        }

        return $view;
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
