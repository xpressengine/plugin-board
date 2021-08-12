<?php

namespace Xpressengine\Plugins\Board;

class TabMenuHandler
{
    /**
     * Boot
     */
    public static function boot()
    {
        app()->singleton(TabMenuHandler::class, function () {
            return new TabMenuHandler();
        });
    }

    /**
     * @return TabMenuHandler
     */
    public static function make(): TabMenuHandler
    {
        return app(TabMenuHandler::class);
    }

    /**
     * 등록된 Tab Menu 반환
     *
     * @param string $id
     * @return mixed
     */
    public function all(string $id): array
    {
        return array_sort(\XeRegister::get($id), function (TabMenu $tabMenu) {
            return $tabMenu->getOrdering();
        });
    }

    public function allActivated(string $id): array
    {
        return array_filter($this->all($id), function (TabMenu $tabMenu) {
            return $tabMenu->getDisplay() === true;
        });
    }

    /**
     * Add Tab Menu
     *
     * @param string $id
     * @param $menu
     */
    public function add(string $id, $menu)
    {
        if (is_array($menu)) {
            $menu = TabMenu::make($menu);
        }

        if (!($menu instanceof TabMenu)) {
            throw new \InvalidArgumentException("Not Tab Menu");
        }

        \XeRegister::push($id, $menu->getId(), $menu);
    }
}
