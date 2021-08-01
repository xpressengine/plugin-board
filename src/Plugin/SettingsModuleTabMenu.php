<?php

namespace Xpressengine\Plugins\Board\Plugin;

use XeRegister;
use Xpressengine\Menu\Models\MenuItem;
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
            'config' => $this->getConfigMenu(),
            'permission' => $this->getPermissionMenu(),
            'toggleMenu' => $this->getToggleMenu(),
            'skin' => $this->getSkinMenu(),
            'editor' => $this->getEditorMenu(),
            'columns' => $this->getColumnsMenu(),
            'dynamicField'  => $this->getDynamicFieldMenu(),
            'settingsExternalLink' => $this->getSettingExternalLink(),
            'boardExternalLink' => $this->getBoardExternalLink(),
            'docsExternalLink' => $this->getDocsExternalLink()
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
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl('config', compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 권한 옵션
     *
     * @return array
     */
    private function getPermissionMenu()
    {
        return [
            'title' => xe_trans('xe::permission'),
            'ordering' => 1,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl('permission', compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 토글 메뉴 옵션
     *
     * @return array
     */
    private function getToggleMenu()
    {
        return [
            'title' => xe_trans('xe::toggleMenu'),
            'ordering' => 2,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl('toggleMenu', compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 스킨 옵션
     *
     * @return array
     */
    private function getSkinMenu()
    {
        return [
            'title' => xe_trans('xe::skin'),
            'ordering' => 3,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl('skin', compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 에디터 욥션
     *
     * @return array
     */
    private function getEditorMenu()
    {
        return [
            'title' => xe_trans('xe::editor'),
            'ordering' => 4,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl('editor', compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 출력 순서 옵션
     *
     * @return array
     */
    private function getColumnsMenu()
    {
        return [
            'title' => xe_trans('board::outputOrder'),
            'ordering' => 5,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl('columns', compact('boardId'));
            }
        ];
    }

    /**
     * 게시판 출력 순서 옵션
     *
     * @return array
     */
    private function getDynamicFieldMenu()
    {
        return [
            'title' => xe_trans('xe::dynamicField'),
            'ordering' => 6,
            'link_func' => function ($boardId) {
                return $this->boardUrlHandler->managerUrl('dynamicField', compact('boardId'));
            }
        ];
    }

    private function getSettingExternalLink()
    {
        return [
            'title' => '메뉴 설정 페이지 열기',
            'ordering' => 7,
            'external_link' => true,
            'link_func' => function($boardId) {
                if ($menuItem = MenuItem::find($boardId)) {
                    return route('settings.menu.edit.item', [$menuItem->menu_id, $menuItem->id]);
                }

                return null;
            }
        ];
    }

    public function getBoardExternalLink()
    {
        return [
            'title' => '게시판 페이지 열기',
            'ordering' => 8,
            'external_link' => true,
            'link_func' => function($boardId) {
                if ($menuItem = MenuItem::find($boardId)) {
                    return \URL::to($menuItem->getAttribute('url'));
                }

                return null;
            }
        ];
    }

    public function getDocsExternalLink()
    {
        return [
            'title' => '게시물 관리하기',
            'ordering' => 9,
            'external_link' => true,
            'link_func' => function($boardId) {
                return route('settings.board.board.docs.index', ['search_board' => $boardId]);
            }
        ];
    }
}
