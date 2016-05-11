<?php
/**
 * DefaultSkin
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
 * DefaultSkin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class DefaultSkin extends AbstractSkin
{
    protected static $skinAlias = 'board::views.defaultSkin';

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

        $this->data['skinAlias'] = static::$skinAlias;

        // wrapped by _frame.blade.php
        $view = View::make(sprintf('%s._frame', static::$skinAlias), $this->data);
        $view->content = View::make(
            sprintf('%s.%s', static::$skinAlias, $this->view),
            $this->data
        )->render();

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


    /**
     * get setting view
     *
     * @param array $config config
     */
    public static function getSettingView($config = [])
    {
       return '';
    }

    /**
     * index customizer
     *
     * @return void
     */
    protected function indexCustomizer()
    {
        $this->setDynamicFieldSkins();
        //$this->setBoardOrderItems();
        $this->setPaginationPresenter();
        $this->setBoardList();
    }

    /**
     * show customizer
     *
     * @return void
     */
    protected function showCustomizer()
    {
        $this->setDynamicFieldSkins();
        //$this->setBoardOrderItems();
        $this->setPaginationPresenter();
        $this->setBoardList();
    }

    /**
     * create customizer
     *
     * @return void
     */
    protected function createCustomizer()
    {
        $this->setDynamicFieldSkins();
    }

    /**
     * create customizer
     *
     * @return void
     */
    protected function editCustomizer()
    {
        $this->setDynamicFieldSkins();
    }

    /**
     * replace dynamicField skins
     *
     * @return void
     */
    protected function setDynamicFieldSkins()
    {
        // replace dynamicField skin registered information
        /** @var \Xpressengine\Register\Container $register */
        $register = app('xe.register');
        $register->set('FieldType/xpressengine@Category/FieldSkin/xpressengine@default', DesignSelectSkin::class);
    }

    /**
     * set pagination presenter
     *
     * @return void
     */
    protected function setPaginationPresenter()
    {
        $this->data['paginate']->setPath($this->data['urlHandler']->get('index'));
        $this->data['paginationPresenter'] = new PaginationPresenter($this->data['paginate']);
        $this->data['paginationMobilePresenter'] = new PaginationMobilePresenter($this->data['paginate']);
    }

    /**
     * set board list
     *
     * @return void
     */
    protected function setBoardList()
    {
        $instanceConfig = InstanceConfig::instance();
        $instanceId = $instanceConfig->getInstanceId();

        $configHandler = app('xe.board.config');
        $boards = $configHandler->gets();
        $boardList = [];
        /** @var ConfigEntity $config */
        foreach ($boards as $config) {
            // 현재의 게시판은 리스트에서 제외
            if ($instanceId === $config->get('boardId')) {
                continue;
            }

            $boardList[] = [
                'value' => $config->get('boardId'),
                'text' => $config->get('boardName'),
            ];
        }
        $this->data['boardList'] = $boardList;
    }
}
