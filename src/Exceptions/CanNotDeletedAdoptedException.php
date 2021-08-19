<?php

namespace Xpressengine\Plugins\Board\Exceptions;

use Illuminate\Http\Response;
use Xpressengine\Plugins\Board\HttpBoardException;

class CanNotDeletedAdoptedException extends HttpBoardException
{
    /**
     * @var string
     */
    protected $message = 'board::canNotDeletedAdopted';

    /**
     * @var int
     */
    protected $statusCode = Response::HTTP_NOT_ACCEPTABLE;
}