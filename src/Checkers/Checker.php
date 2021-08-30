<?php

namespace Xpressengine\Plugins\Board\Checkers;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\User\UserInterface;

interface Checker
{
    public function operation(Board $board, UserInterface $user): bool;
}