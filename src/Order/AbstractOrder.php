<?php
/**
 *
 */
namespace Xpressengine\Plugins\Board\Order;

use Xpressengine\Plugin\ComponentTrait;
use Xpressengine\Plugin\ComponentInterface;

/**
 * Class AbstractOrder
 * @package Xpressengine\Plugins\Board\Extensions
 */
abstract class AbstractOrder implements ComponentInterface
{
    use ComponentTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * get name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * get description
     *
     * @return string
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * change wheres, orders parameters
     *
     *
     * @param array $wheres 검색 조건
     * @param array $orders 정렬 조건
     * @return void
     */
    abstract public function make(&$wheres, &$orders);

    /**
     * @return void
     */
    public static function boot()
    {

    }
}
