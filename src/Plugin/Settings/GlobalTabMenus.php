<?php

namespace Xpressengine\Plugins\Board\Plugin\Settings;

use XeRegister;
use Xpressengine\Plugins\Board\TabMenu;
use Xpressengine\Plugins\Board\TabMenuHandler;
use Xpressengine\Plugins\Board\UrlHandler as BoardUrlHandler;

abstract class GlobalTabMenus
{
    const ID = 'settings/board/global/tab_menu';

    /**
     * Boot
     */
    public static function boot()
    {
        static::add(static::getConfigMenu());
        static::add(static::getPermissionMenu());
        static::add(static::getToggleMenu());
        static::add(static::getDocsExternalLink());
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
     * get config tab menu
     *
     * @return TabMenu
     */
    private static function getConfigMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('config')
            ->setTitle('board::boardDetailConfigures')
            ->setOrderNumber(0)
            ->setLinkFunction(function () {
                return app(BoardUrlHandler::class)->managerUrl(sprintf('global.%s', 'config'));
            });
    }

    /**
     * get permission tab menu
     *
     * @return TabMenu
     */
    private static function getPermissionMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('permission')
            ->setTitle('xe::permission')
            ->setOrderNumber(1)
            ->setLinkFunction(function () {
                return app(BoardUrlHandler::class)->managerUrl(sprintf('global.%s', 'permission'));
            });
    }

    /**
     * get toggle tab menu
     *
     * @return TabMenu
     */
    private static function getToggleMenu(): TabMenu
    {
        return TabMenu::make()
            ->setId('toggleMenu')
            ->setTitle('xe::toggleMenu')
            ->setOrderNumber(2)
            ->setLinkFunction(function () {
                return app(BoardUrlHandler::class)->managerUrl(sprintf('global.%s', 'toggleMenu'));
            });
    }

    /**
     * get Docs External Link
     *
     * @return TabMenu
     */
    public static function getDocsExternalLink(): TabMenu
    {
        $title = sprintf("%s > %s > %s",
            xe_trans('xe::contents'),
            xe_trans('board::board'),
            xe_trans('board::articlesManage')
        );

        return TabMenu::make()
            ->setId('docsExternalLink')
            ->setTitle($title)
            ->setOrderNumber(3)
            ->setIcon('xi-external-link')
            ->setIsExternalLink(true)
            ->setLinkFunction(function () {
                return route('settings.board.board.docs.index');
            });
    }
}
