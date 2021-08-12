<?php

namespace Xpressengine\Plugins\Board\Plugin\Settings;

use XeRegister;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\TabMenu;
use Xpressengine\Plugins\Board\TabMenuHandler;
use Xpressengine\Plugins\Board\UrlHandler as BoardUrlHandler;

abstract class InstanceTabMenus
{
    const ID = 'settings/board/instance/menu';

    public static function boot()
    {
        static::add(static::getConfigMenu());
        static::add(static::getPermissionMenu());
        static::add(static::getToggleMenu());
        static::add(static::getSkinMenu());
        static::add(static::getEditorMenu());
        static::add(static::getColumnsMenu());
        static::add(static::getDynamicFieldMenu());
        static::add(static::getSettingExternalLink());
        static::add(static::getBoardExternalLink());
        static::add(static::getDocsExternalLink());
        static::add(static::getCommentExternalLink());
        static::add(static::getCategoryExternalLink());
    }

    /**
     * add Menu
     *
     * @param TabMenu $tabMenu
     */
    public static function add(TabMenu $tabMenu)
    {
        TabMenuHandler::make()->add(static::ID, $tabMenu);
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        return TabMenuHandler::make()->allActivated(static::ID);
    }

    /**
     * get config' tab menu
     *
     * @return TabMenu
     */
    private static function getConfigMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('config')
            ->setTitle('board::boardDetailConfigures')
            ->setOrderNumber(0)
            ->setLinkFunction(function ($boardId) {
                return app(BoardUrlHandler::class)->managerUrl('config', compact('boardId'));
            });
    }

    /**
     * get permission's tab menu
     *
     * @return TabMenu
     */
    private static function getPermissionMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('permission')
            ->setTitle('xe::permission')
            ->setOrderNumber(1)
            ->setLinkFunction(function ($boardId) {
                return app(BoardUrlHandler::class)->managerUrl('permission', compact('boardId'));
            });
    }

    /**
     * get toggle's tab menu
     *
     * @return TabMenu
     */
    private static function getToggleMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('toggleMenu')
            ->setTitle('xe::toggleMenu')
            ->setOrderNumber(2)
            ->setLinkFunction(function ($boardId) {
                return app(BoardUrlHandler::class)->managerUrl('toggleMenu', compact('boardId'));
            });
    }

    /**
     * get skin's tab menu
     *
     * @return TabMenu
     */
    private static function getSkinMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('skin')
            ->setTitle('xe::skin')
            ->setOrderNumber(3)
            ->setLinkFunction(function ($boardId) {
                return app(BoardUrlHandler::class)->managerUrl('skin', compact('boardId'));
            });
    }

    /**
     * get editor's tab menu
     *
     * @return TabMenu
     */
    private static function getEditorMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('editor')
            ->setTitle('xe::editor')
            ->setOrderNumber(4)
            ->setLinkFunction(function ($boardId) {
                return app(BoardUrlHandler::class)->managerUrl('editor', compact('boardId'));
            });
    }

    /**
     * get columns tab menu
     *
     * @return TabMenu
     */
    private static function getColumnsMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('columns')
            ->setTitle('board::outputOrder')
            ->setOrderNumber(5)
            ->setLinkFunction(function ($boardId) {
                return app(BoardUrlHandler::class)->managerUrl('columns', compact('boardId'));
            });
    }

    /**
     * get Dynamic Field's Tab Menus
     *
     * @return TabMenu
     */
    private static function getDynamicFieldMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('dynamicField')
            ->setTitle('xe::dynamicField')
            ->setOrderNumber(6)
            ->setLinkFunction(function ($boardId) {
                return app(BoardUrlHandler::class)->managerUrl('dynamicField', compact('boardId'));
            });
    }

    /**
     * get Setting External Link's Tab Menu
     *
     * @return TabMenu
     */
    private static function getSettingExternalLink(): TabMenu
    {
        $title = sprintf("%s %s",
            xe_trans('xe::menu'),
            xe_trans('xe::editItem')
        );

        return TabMenu::make()
            ->setId('settingsExternalLink')
            ->setTitle($title)
            ->setOrderNumber(7)
            ->setIcon('xi-external-link')
            ->setIsExternalLink(true)
            ->setLinkFunction(function ($boardId) {
                if ($menuItem = MenuItem::find($boardId)) {
                    return route('settings.menu.edit.item', [$menuItem->menu_id, $menuItem->id]);
                }

                return null;
            });
    }

    /**
     * get Board External Link's Tab Menu
     *
     * @return TabMenu
     */
    private static function getBoardExternalLink(): TabMenu
    {
        $title = sprintf("%s %s",
            xe_trans('board::board'),
            xe_trans('xe::goLink')
        );

        return TabMenu::make()
            ->setId('boardExternalLink')
            ->setTitle($title)
            ->setOrderNumber(8)
            ->setIcon('xi-external-link')
            ->setIsExternalLink(true)
            ->setLinkFunction(function ($boardId) {
                if ($menuItem = MenuItem::find($boardId)) {
                    return \URL::to($menuItem->getAttribute('url'));
                }

                return null;
            });
    }

    /**
     * get Docs External Link's Tab Menu
     *
     * @return TabMenu
     */
    private static function getDocsExternalLink(): TabMenu
    {
        $title = sprintf("%s > %s > %s",
            xe_trans('xe::contents'),
            xe_trans('board::board'),
            xe_trans('board::articlesManage')
        );

        return TabMenu::make()
            ->setId('docsExternalLink')
            ->setTitle($title)
            ->setOrderNumber(9)
            ->setIcon('xi-external-link')
            ->setIsExternalLink(true)
            ->setLinkFunction(function ($boardId) {
                return route('settings.board.board.docs.index', ['search_board' => $boardId]);
            });
    }

    /**
     * get Comment External Link's Tab Menu
     *
     * @return TabMenu
     */
    private static function getCommentExternalLink(): TabMenu
    {
        return TabMenu::make()
            ->setId('commentExternalLink')
            ->setTitle('comment::manage.detailSetting')
            ->setOrderNumber(10)
            ->setIcon('xi-external-link')
            ->setIsExternalLink(true)
            ->setLinkFunction(function ($boardId) {
                /** @var ConfigHandler $boardConfigHandler */
                $boardConfigHandler = app(ConfigHandler::class);
                $boardConfig = $boardConfigHandler->get($boardId);

                if ($boardConfig->get('comment')) {
                    return route('manage.comment.setting', ['targetInstanceId' => $boardId]);
                }

                return null;
            });
    }

    /**
     * get Category External Link's Tab Menu
     *
     * @return TabMenu
     */
    private static function getCategoryExternalLink(): TabMenu
    {
        return TabMenu::make()
            ->setId('categoryExternalLink')
            ->setTitle('board::categoryManage')
            ->setOrderNumber(11)
            ->setIcon('xi-external-link')
            ->setIsExternalLink(true)
            ->setLinkFunction(function ($boardId) {
                /** @var ConfigHandler $boardConfigHandler */
                $boardConfigHandler = app(ConfigHandler::class);
                $boardConfig = $boardConfigHandler->get($boardId);

                $category = $boardConfig->get('category');
                $categoryId = $boardConfig->get('categoryId');

                if ($category && $categoryId) {
                    return route('manage.category.show', $categoryId);
                }

                return null;
            });
    }
}
