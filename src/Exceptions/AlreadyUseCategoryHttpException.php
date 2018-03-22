<?php
/**
 * AlreadyUseCategoryHttpException
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (sirwoongke) <sirwoongke@xpressengine.com>
 * @copyright   2018 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Exceptions;

use Illuminate\Http\Response;
use Xpressengine\Plugins\Board\HttpBoardException;

/**
 * AlreadyUseCategoryHttpException
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (sirwoongke) <sirwoongke@xpressengine.com>
 * @copyright   2018 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
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
