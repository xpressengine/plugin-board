<?php
/**
 *
 */
namespace Xpressengine\Plugins\Board\Addon;

use Xpressengine\Plugin\ComponentTrait;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugin\PluginRegister;
use Xpressengine\Plugins\Board\Exceptions\InvalidConfigException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundAddonException;
use Xpressengine\Plugins\Board\ItemEntity;
use Xpressengine\Plugin\ComponentInterface;

/**
 * Addon
 *
 * * 게시판 Addon
 * * Controller, View 에서 사용
 *
 * @package Xpressengine\Plugins\Board\Extensions
 */
class AddonManager
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
     * @param array        $addons 정렬 조건
     * @return void
     */
    public function make(ConfigEntity $config, array $inputs, array &$wheres, array &$addons)
    {
        $addons = [];

        $boardAddon = null;
        if (empty($inputs['orderType'])) {
            $boardAddon = $this->get($config->get('addon'));
        } else {
            $boardAddon = $this->get($inputs['addonType']);
        }

        if ($boardAddon !== null) {
            $boardAddon->make($wheres, $addons);
        } else {
            // 어떤 걸로 하면 decode 했을 때 array 가 될 수 있는걸 문자열로 넘길 수있나
            // $inputs['addonType'] 으로 $addons 를 변환 할 수 있나
        }
    }

    /**
     * id 로 class instance 반환
     *
     * @param string $id addon class id
     * @return Abstractaddon
     */
    public function get($id)
    {
        if ($id === null) {
            throw new InvalidConfigException;
        }
        $class = $this->register->get($id);
        if ($class === null) {
            throw new NotFoundAddonException;
        }
        return new $class;
    }

    /**
     * 등록된 addon 반환
     *
     * @return array
     */
    public function gets()
    {
        $names = $this->register->get('module/board@board/addon');
        $classes = [];
        foreach ($names as $id => $name) {
            $classes[$id] = new $name;
        }
        return $classes;
    }
}
