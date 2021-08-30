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

use View;
use XePresenter;
use Illuminate\Support\Arr;
use Xpressengine\Plugins\Board\Plugin\Settings\GlobalTabMenus;
use Xpressengine\Plugins\Board\Plugin\Settings\InstanceTabMenus;
use Xpressengine\Presenter\Presenter;
use Xpressengine\Skin\AbstractSkin;

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
        $info = explode('.', $this->view);
        $type = Arr::get($info, 0);

        if (XePresenter::getRenderType() == Presenter::RENDER_CONTENT) {
            return View::make(sprintf('%s.%s', static::$skinAlias, $this->view), $this->data);
        }

        if (is_string($type) && $this->isSupportFrame($type)) {
            return $this->getContentWithFrame($info);
        }

        return View::make(sprintf('%s.%s', static::$skinAlias, $this->view), $this->data);
    }

    /**
     * get content with frame
     *
     * @param array $info
     * @return mixed
     */
    private function getContentWithFrame(array $info)
    {
        $type = Arr::get($info, 0);
        $action =  Arr::get($this->data, '_active', Arr::get($info, 1));

        $this->data['afea'] = 1;
        $this->data['_menus'] = $this->isModuleFrame($type) ? InstanceTabMenus::all() : GlobalTabMenus::all();

        if (array_has($this->data, '_active') === false) {
            $this->data['_active'] = $action;
        }

        $this->data['_activeMenu'] = $this->data['_menus'][$action];

        if ($this->isModuleFrame($type)) {
            if (!array_key_exists('config', $this->data)) {
                $this->data['config'] = $this->data['configHandler']->get($this->data['boardId']);
            }
        }

        $contentView = $this->data['_activeMenu']->getContent($this->data, View::make(
            sprintf('%s.%s', static::$skinAlias, $this->view),
            $this->data
        ));

        $view = View::make(sprintf('%s.%s._frame', static::$skinAlias, $type), $this->data);
        $view->content = $contentView->render();

        return $view;
    }

    /**
     * is support frame
     *
     * @param string|null $type
     * @return bool
     */
    private function isSupportFrame(string $type): bool
    {
        return $this->isModuleFrame($type)
            || $this->isGlobalFrame($type);
    }

    /**
     * is using module frame
     *
     * @param string $type
     * @return bool
     */
    private function isModuleFrame(string $type): bool
    {
        return $type === 'module';
    }

    /**
     * is using global frame
     *
     * @param string $type
     * @return bool
     */
    private function isGlobalFrame(string $type): bool
    {
        return $type === 'global';
    }
}
