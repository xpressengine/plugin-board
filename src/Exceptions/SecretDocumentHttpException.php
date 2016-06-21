<?php
/**
 * SecretDocumentHttpException
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Exceptions;

use Xpressengine\Plugins\Board\HttpBoardException;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecretDocumentHttpException
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class SecretDocumentHttpException extends HttpBoardException
{
    protected $message = 'board::SecretStatePost';
    protected $statusCode = Response::HTTP_FORBIDDEN;
}
