<?php

namespace Xpressengine\Plugins\Board\Checkers\Logics;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\User\UserInterface;

class IsOwnerLogic extends CheckerLogic
{
    public function operation(Board $board, UserInterface $user): bool
    {
        if ($board->user_id !== $user->getId()) {
            return false;
        }

        return parent::operation($board, $user);
    }
}