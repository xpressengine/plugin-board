<?php
/**
 * Board extension handler
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
namespace Xpressengine\Plugins\Board;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugin\PluginRegister;

/**
 * Board extension handler
 * 게시판의 확장 기능 class 관리
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class ExtensionHandler
{
    const REGISTER_NAME = 'extension';
    const ORDER_REGISTER_NAME = 'order';

    /**
     * @var PluginRegister
     */
    protected $register;

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * create instance
     *
     * @param PluginRegister $register register's container
     */
    public function __construct(PluginRegister $register)
    {
        $this->register = $register;
    }

    /**
     * 확장 기능 목록
     *
     * @return array
     */
    public function getsExtensions()
    {
        $names = $this->register->get(Module\Board::getId() . PluginRegister::KEY_DELIMITER . self::REGISTER_NAME);
        $classes = [];
        foreach ($names as $name) {
            $this->extensions[$name] = new $name;
        }
        return $this->extensions;
    }

    /**
     * 확장 기능 반화
     *
     * @return Extensions\ExtensionInterface
     */
    public function get($name)
    {

    }

    /**
     * 정렬 확장 기능 목록
     *
     * @return array
     */
    public function getOrders()
    {
        $names = $this->register->get(Module\Board::getId() . PluginRegister::KEY_DELIMITER . self::ORDER_REGISTER_NAME);
        $classes = [];
        foreach ($names as $name) {
            $classes[$name] = new $name;
        }
        return $classes;
    }

    /**
     * board config 에서 extension class 반환
     *
     * @param ConfigEntity $config board config entity
     * @return array
     */
    public function gets(ConfigEntity $config)
    {
        $names = $config->get('extensions');
        $classes = [];
        if (count($names) > 0) {
            foreach ($names as $name) {
                $className = '\\' . $name;
                $classes[] = new $className;
            }
        }
        return $classes;
    }

    /**
     * extension activate 인터페이스 실행
     *
     * @param ConfigEntity $config     board config entity
     * @param array        $extensions extension class names
     * @return void
     */
    public function activate(array $extensions, ConfigEntity $config)
    {
        foreach ($extensions as $name) {
            $className = '\\' . $name;
            /** @var Extensions\ExtensionInterface $class */
            $class = new $className;
            $class->activate($config);
        }
    }
}
