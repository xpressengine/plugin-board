<?php
/**
 * SettingsSkin
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Board\Components\Skins\Board\Settings\Common;

use Xpressengine\Plugins\Board\Plugin\SettingsModuleTabMenu;
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
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class CommonSettingsSkin extends AbstractSkin
{
    /**
     * @var string
     */
    public static $skinAlias = 'board/components/Skins/Board/Settings/Common/views';

    /**
     * render
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
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
            if ($subPath === 'module') {
                $this->setModuleData();
            }

            else if ($subPath === 'global') {
                $this->data['_menu'] = app('xe.board.settings_global_tab_menu')->get();
            }

            // wrapped by _frame.blade.php
            $this->data['afea'] = 1;
            $view = View::make(sprintf('%s.%s._frame', static::$skinAlias, $subPath), $this->data);
            $view->content = $contentView->render();
        } else {
            $view = $contentView;
        }

        return $view;
    }

    private function setModuleData()
    {
        /** @var SettingsModuleTabMenu $settingsModuleTabMenu */
        $settingsModuleTabMenu = app('xe.board.settings_module_tab_menu');

        if (!array_key_exists('config', $this->data)) {
            $this->data['config'] = $this->data['configHandler']->get($this->data['boardId']);
        }

        $this->data['_menu'] = $settingsModuleTabMenu->get();
    }
}
