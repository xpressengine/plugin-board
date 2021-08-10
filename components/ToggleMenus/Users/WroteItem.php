<?php

namespace Xpressengine\Plugins\Board\Components\ToggleMenus\Users;

use App\ToggleMenus\User\UserToggleMenu;
use Xpressengine\User\Models\User;

class WroteItem extends UserToggleMenu
{
    /** @var string */
    protected static $id = 'user/toggleMenu/board@wroteItem';

    /** @var string */
    private $boardInstanceId;

    public function __construct()
    {
        $this->setBoardInstanceId();
    }
    
    protected function setBoardInstanceId()
    {
        $menuItem = null;

        $path = parse_url(url()->previous())['path'];
        $moduleUrl = array_get(explode('/', $path), 1);

        if (empty($moduleUrl)) {
            $menuItem = app('xe.menu')->items()->query()->where('type', 'board@board')->where('id', app('xe.site')->getHomeInstanceId())->first();
        }
        else {
            $menuItem = app('xe.menu')->items()->query()->where('type', 'board@board')->where('url', $moduleUrl)->first();
        }

        if ($menuItem !== null) {
            $this->boardInstanceId = $menuItem->getAttribute('id');
        }
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return '작성 글 보기';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return static::MENUTYPE_LINK;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        $user = User::find($this->identifier);
        return instance_route('index', ['writer' => $user->display_name], $this->boardInstanceId);
    }

    /**
     * @return bool
     */
    public function allows() : bool
    {
        return $this->boardInstanceId !== null;
    }

    /**
     * @return string|null
     */
    public function getScript()
    {
        return null;
    }
}