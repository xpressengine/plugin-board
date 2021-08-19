<?php

namespace Xpressengine\Plugins\Board\Exceptions;

use Illuminate\Http\Response;
use Xpressengine\Plugins\Board\HttpBoardException;

class AlreadyAdoptedException extends HttpBoardException
{
    /**
     * @var string
     */
    protected $message = 'board::alreadyAdopted';

    /**
     * @var int
     */
    protected $statusCode = Response::HTTP_NOT_ACCEPTABLE;
}