<?php

namespace Xpressengine\Plugins\Board\Checkers\Logics;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Checkers\Checker;
use Xpressengine\User\UserInterface;

class CheckerLogic implements Checker
{
    /** @var Checker $validatedChecker */
    protected $validatedChecker;

    /**
     * Validated Checker Logic constructor.
     *
     * @param Checker $validatedChecker
     */
    public function __construct(Checker $validatedChecker)
    {
        $this->setValidatedChecker($validatedChecker);
    }

    /**
     * set checker
     *
     * @param Checker $validatedChecker
     * @return Checker
     */
    public function setValidatedChecker(Checker $validatedChecker): Checker
    {
        $this->validatedChecker = $validatedChecker;
        return $this;
    }

    /**
     * operation
     *
     * @param Board $board
     * @param UserInterface $user
     * @return bool
     */
    public function operation(Board $board, UserInterface $user): bool
    {
        return $this->validatedChecker->operation($board, $user);
    }
}