<?php
/**
 * AlreadyUseCategoryHttpException
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Board\Exceptions;

use Illuminate\Http\Response;
use Xpressengine\Plugins\Board\HttpBoardException;

/**
 * AlreadyUseCategoryHttpException
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class AlreadyUseCategoryHttpException extends HttpBoardException
{
    /**
     * @var string
     */
    protected $message = 'board::alreadyUseCategory';

    /**
     * @var int
     */
    protected $statusCode = Response::HTTP_NOT_ACCEPTABLE;
}
