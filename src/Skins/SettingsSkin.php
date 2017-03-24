<?php
/**
 * SettingsSkin
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
namespace Xpressengine\Plugins\Board\Skins;

use Xpressengine\Presenter\Presenter;
use Xpressengine\Skin\AbstractSkin;
use View;
use XePresenter;

/**
 * SettingsSkin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class SettingsSkin extends AbstractSkin
{
    /**
     * @var string
     */
    protected static $skinAlias = 'board::views.defaultSettings';

    /**
     * render
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
//        $view = View::make(sprintf('board::views.defaultSettings.%s', $this->view), $this->data);

        $contentView = View::make(
            sprintf('%s.%s', static::$skinAlias, $this->view),
            $this->data
        );

        $parts = pathinfo($contentView->getPath());
        $names = explode('/', $parts['dirname']);
        $subPath =array_pop($names);
        $active = substr($parts['filename'], 0, stripos($parts['filename'], '.'));
        $this->data['_active'] = $active;

        if (XePresenter::getRenderType() == Presenter::RENDER_CONTENT) {
            $view = $contentView;
        } elseif($subPath === 'global' || $subPath === 'module') {
            // wrapped by _frame.blade.php
            $this->data['afea'] = 1;
            $view = View::make(sprintf('%s.%s._frame', static::$skinAlias, $subPath), $this->data);
            $view->content = $contentView->render();
        } else {
            $view = $contentView;
        }

        return $view;
    }
}
