<?php
/**
 * Board Extensions
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
namespace Xpressengine\Plugins\Board\Order;

use Xpressengine\Plugin\RegistrableTrait;

/**
 * Extension order by recently updated document
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class RecentlyUpdated extends AbstractOrder
{
    /**
     * @var string
     */
    protected static $id = 'module/board@board/order/board@recentlyUpdated';

    protected $name = 'board::recentlyUpdated';
    protected $description = 'board::recentlyUpdatedDescription';

    /**
     * change wheres, orders parameters
     *
     * @param array $wheres 검색 조건
     * @param array $orders 정렬 조건
     * @return void
     */
    public function make(&$wheres, &$orders)
    {
        $orders = ['updatedAt' => 'desc'];
    }
}
