<?php
/**
 *
 */
namespace Xpressengine\Plugins\Board\Order;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugin\PluginRegister;
use Xpressengine\Plugins\Board\Exceptions\InvalidConfigException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundOrderException;

/**
 * Class OrderManager
 * @package Xpressengine\Plugins\Board\Order
 */
class OrderManager
{

    /**
     * @var PluginRegister
     */
    protected $register;

    /**
     * @param PluginRegister $register
     */
    public function __construct(PluginRegister $register)
    {
        $this->register = $register;
    }

    /**
     * inputs
     *
     * @param ConfigEntity $config board config entity
     * @param array        $inputs request parameters
     * @param array        $wheres 검색 조건
     * @param array        $orders 정렬 조건
     * @return void
     */
    public function make(ConfigEntity $config, array $inputs, array &$wheres, array &$orders)
    {
        $orders = [];

        $boardOrder = null;
        if (empty($inputs['orderType'])) {
            $boardOrder = $this->get($config->get('order'));
        } else {
            $boardOrder = $this->get($inputs['orderType']);
        }

        if ($boardOrder !== null) {
            $boardOrder->make($wheres, $orders);
        } else {
            // 어떤 걸로 하면 decode 했을 때 array 가 될 수 있는걸 문자열로 넘길 수있나
            // $inputs['orderType'] 으로 $orders 를 변환 할 수 있나
        }
    }

    /**
     * id 로 class instance 반환
     *
     * @param string $id order class id
     * @return AbstractOrder
     */
    public function get($id)
    {
        if ($id === null) {
            throw new InvalidConfigException;
        }
        $class = $this->register->get($id);
        if ($class === null) {
            throw new NotFoundOrderException;
        }
        return new $class;
    }

    /**
     * 등록된 order 반환
     *
     * @return array
     */
    public function gets()
    {
        $names = $this->register->get('module/board@board/order');
        $classes = [];
        foreach ($names as $id => $name) {
            $classes[$id] = new $name;
        }
        return $classes;
    }
}
