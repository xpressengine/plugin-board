<?php

namespace Xpressengine\Plugins\Board\Plugin;

use XeRegister;
use Xpressengine\Plugins\Board\UrlHandler as BoardUrlHandler;

/**
 * 2021-03-05 관리자 페이지의 게시판 탭 메뉴를 동적으로 관리하도록 코드 변경
 */
final class SettingsGlobalTabMenu
{
    const KEY = 'settings/board_global/menu';

    /** @var BoardUrlHandler $boardUrlHandler */
    private $boardUrlHandler;

    public function __construct()
    {
        $this->boardUrlHandler = app(BoardUrlHandler::class);
    }

    /**
     * Board Instance Tab 메뉴 설정
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->getMenus() as $id => $menu) {
            XeRegister::push(self::KEY, $id, $menu);
        }
    }

    /**
     * Board Instance Tab 메뉴 설정된 값 반환
     *
     * @return mixed
     */
    public function get()
    {
        return XeRegister::get(static::KEY);
    }

    /**
     * 인기검색과 관련된 메뉴 반환
     *
     * @return array[]
     */
    private function getMenus()
    {
        return [
            'config' => $this->getConfigMenu(),
            'permission' => $this->getPermissionMenu(),
            'toggleMenu' => $this->getToggleMenu(),
            'docsExternalLink' => $this->getDocsExternalLink(),
        ];
    }

    /**
     * 게시판 상세 설정 옵션
     *
     * @return array
     */
    private function getConfigMenu()
    {
        return [
            'title' => xe_trans('board::boardDetailConfigures'),
            'ordering' => 0,
            'link_func' => function () {
                return $this->boardUrlHandler->managerUrl(sprintf('global.%s', 'config'));
            }
        ];
    }
    
    /**
     * 게시판 권한 설정 옵션
     *
     * @return array
     */
    private function getPermissionMenu()
    {
        return [
            'title' => xe_trans('xe::permission'),
            'ordering' => 1,
            'link_func' => function () {
                return $this->boardUrlHandler->managerUrl(sprintf('global.%s', 'permission'));
            }
        ];
    }

    /**
     * 게시판 토글 메뉴 설정 옵션
     *
     * @return array
     */
    private function getToggleMenu()
    {
        return [
            'title' => xe_trans('xe::toggleMenu'),
            'ordering' => 2,
            'link_func' => function () {
                return $this->boardUrlHandler->managerUrl(sprintf('global.%s', 'toggleMenu'));
            }
        ];
    }

    /**
     * 게시물 관리하기
     *
     * @return array
     */
    public function getDocsExternalLink()
    {
        return [
            'title' => '게시물 관리하기',
            'ordering' => 3,
            'external_link' => true,
            'link_func' => function() {
                return route('settings.board.board.docs.index');
            }
        ];
    }
}
