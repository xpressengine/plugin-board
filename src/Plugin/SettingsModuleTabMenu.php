<?php

namespace Xpressengine\Plugins\Board\Plugin;

use XeRegister;
use Xpressengine\Plugins\Board\UrlHandler as BoardUrlHandler;

/**
 * 2021-02-20 관리자 페이지의 게시판 탭 메뉴를 동적으로 관리하도록 코드 변경
 */
final class SettingsModuleTabMenu
{
    const KEY = 'settings/board_instance/menu';

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
            $this->getConfigTabMenuKey()        => $this->getConfigTabMenuOption(),
            $this->getPermissionTabMenuKey()    => $this->getPermissionTabMenuOption(),
            $this->getToggleMenuTabMenuKey()    => $this->getToggleMenuTabMenuOption(),
            $this->getSkinTabMenuKey()          => $this->getSkinTabMenuOption(),
            $this->getEditorTabMenuKey()        => $this->getEditorTabMenuOption(),
            $this->getColumnsTabMenuKey()       => $this->getColumnsTabMenuOption(),
            $this->getDynamicFieldTabMenuKey()  => $this->getDynamicFieldTabMenuOption(),
        ];
    }

    /**
     * 게시판 상세 설정 키
     *
     * @return string
     */
    private function getConfigTabMenuKey()
    {
        return 'config';
    }

    /**
     * 게시판 상세 설정 옵션
     *
     * @return array
     */
    private function getConfigTabMenuOption()
    {
        return [
            'title' => xe_trans('board::boardDetailConfigures'),
            'ordering' => 0,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl($this->getConfigTabMenuKey(), compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 권한 키
     *
     * @return string
     */
    private function getPermissionTabMenuKey()
    {
        return 'permission';
    }

    /**
     * 게시판 권한 옵션
     *
     * @return array
     */
    private function getPermissionTabMenuOption()
    {
        return [
            'title' => xe_trans('xe::permission'),
            'ordering' => 1,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl($this->getPermissionTabMenuKey(), compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 토글 메뉴 키
     *
     * @return string
     */
    private function getToggleMenuTabMenuKey()
    {
        return 'toggleMenu';
    }

    /**
     * 게시판 토글 메뉴 옵션
     *
     * @return array
     */
    private function getToggleMenuTabMenuOption()
    {
        return [
            'title' => xe_trans('xe::toggleMenu'),
            'ordering' => 2,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl($this->getToggleMenuTabMenuKey(), compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 스킨 키
     *
     * @return string
     */
    private function getSkinTabMenuKey()
    {
        return 'skin';
    }

    /**
     * 게시판 스킨 옵션
     *
     * @return array
     */
    private function getSkinTabMenuOption()
    {
        return [
            'title' => xe_trans('xe::skin'),
            'ordering' => 3,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl($this->getSkinTabMenuKey(), compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 에디터 키
     *
     * @return string
     */
    private function getEditorTabMenuKey()
    {
        return 'editor';
    }

    /**
     * 게시판 에디터 욥션
     *
     * @return array
     */
    private function getEditorTabMenuOption()
    {
        return [
            'title' => xe_trans('xe::editor'),
            'ordering' => 4,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl($this->getEditorTabMenuKey(), compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 출력 순서 옵션
     *
     * @return string
     */
    private function getColumnsTabMenuKey()
    {
        return 'columns';
    }

    /**
     * 게시판 출력 순서 옵션
     *
     * @return array
     */
    private function getColumnsTabMenuOption()
    {
        return [
            'title' => xe_trans('board::outputOrder'),
            'ordering' => 5,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl($this->getColumnsTabMenuKey(), compact('boardId'));
            }
        ];
    }

    /**
     * 확장 필드
     *
     * @return string
     */
    private function getDynamicFieldTabMenuKey()
    {
        return 'dynamicField';
    }

    /**
     * 게시판 출력 순서 옵션
     *
     * @return array
     */
    private function getDynamicFieldTabMenuOption()
    {
        return [
            'title' => xe_trans('xe::dynamicField'),
            'ordering' => 6,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl($this->getDynamicFieldTabMenuKey(), compact('boardId'));
            }
        ];
    }
}
