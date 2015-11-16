<?php
/**
 * Board module
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board\Extensions
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Order;

use Xpressengine\Plugin\RegistrableTrait;

/**
 * Extension order by recently created document
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class RecentlyCreated extends AbstractOrder
{
    protected static $id = 'module/board@board/order/board@recentlyCreated';

    protected $name = 'board::recentlyCreated';
    protected $description = 'board::recentlyCreatedDescription';

    /**
     * change wheres, orders parameters
     *
     * @param array $wheres 검색 조건
     * @param array $orders 정렬 조건
     * @return void
     */
    public function make(&$wheres, &$orders)
    {
        $wheres = array_merge($wheres, []);
        $orders = [
            'head' => 'desc',
            'reply' => 'asc'
        ];
    }
}
