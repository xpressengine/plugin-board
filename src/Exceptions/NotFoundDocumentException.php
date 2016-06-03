<?php
/**
 * Exceptions
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers (akasima) <osh@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Crop. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Exceptions;

use Xpressengine\Plugins\Board\HttpBoardException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NotFoundDocumentException
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 */
class NotFoundDocumentException extends HttpBoardException
{
    protected $statusCode = Response::HTTP_GONE;
    protected $message = 'board::notFoundDocument';
}
