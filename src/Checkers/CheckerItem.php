<?php

namespace Xpressengine\Plugins\Board\Checkers;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\User\UserInterface;

class CheckerItem implements Checker
{
    public function operation(Board $board, UserInterface $user): bool
    {
        return true;
    }
}