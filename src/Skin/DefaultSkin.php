<?php
/**
 * Board user skin
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Skin;

use Xpressengine\Routing\InstanceConfig;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugins\Board\FieldSkins\Category\DesignSelectSkin;
use Xpressengine\Plugins\Board\PaginationPresenter;
use Xpressengine\Plugins\Board\PaginationMobilePresenter;
use Xpressengine\Skin\AbstractSkin;
use View;

/**
 * Board user skin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class DefaultSkin extends AbstractSkin
{
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

        // wrapped by _frame.blade.php
        $view = View::make('board::views.defaultSkin._frame', $this->data);
        $view->content = View::make(
            sprintf('board::views.defaultSkin.%s', $this->view),
            $this->data
        )->render();

        return $view;
    }

    /**
     * index customizer
     *
     * @return void
     */
    protected function indexCustomizer()
    {
        $this->setDynamicFieldSkins();
        $this->setBoardOrderItems();
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
        $this->setBoardOrderItems();
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
        $register->put('FieldType/xpressengine@Category/FieldSkin/xpressengine@default', DesignSelectSkin::class);
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
     * set board order items
     *
     * @return void
     */
    protected function setBoardOrderItems()
    {
        $items = [];
        foreach ($this->data['boardOrders'] as $id => $instance) {
            $items[] = [
                'value' => $id,
                'text' => xe_trans($instance->name()),
            ];
        }

        $this->data['boardOrderItems'] = $items;
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
    
    /**
     * get manage URI
     *
     * @return string
     */
    public static function getSettingsURI()
    {
    }

    /**
     * 스킨 설정을 위한 폼 html 반환
     *
     * @param array $data data
     * @return string
     */
    public function getConfigForm(array $data)
    {
        return View::make('board::views.board.section.form', $data)->render();
    }
}
