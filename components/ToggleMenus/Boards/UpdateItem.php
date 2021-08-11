<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Boards;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\ToggleMenu\AbstractToggleMenu;

class UpdateItem extends AbstractToggleMenu
{
    /** @var string */
    protected static $id = 'module/board@board/toggleMenu/board@updateItem';

    public static function getTitle(): string
    {
        return xe_trans('xe::update');
    }

    public function getText(): string
    {
        return xe_trans('xe::update');
    }

    public function getType(): string
    {
        return static::MENUTYPE_LINK;
    }

    public function getAction(): string
    {
        $params = array_merge(request()->all(), ['id' => $this->identifier]);
        return instance_route('edit', $params, $this->instanceId);
    }

    /**
     * @return null
     */
    public function getScript()
    {
        return null;
    }

    public function allows(): bool
    {
        $board = Board::findOrFail($this->identifier);

        if ($board->getAttribute('user_id') === auth()->id()) {
            return true;
        }

        $configHandler = app('xe.board.config');
        $boardPermission = app('xe.board.permission');

        $config = $configHandler->get($this->instanceId);
        return $config !== null ? $boardPermission->checkManageAction($this->instanceId) : false;
    }
}
