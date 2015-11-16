<?php
/**
 *
 */
namespace Xpressengine\Plugins\Board\Addon;

use Xpressengine\Plugin\ComponentTrait;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugins\Board\ItemEntity;
use Xpressengine\Plugin\ComponentInterface;

/**
 * AbstractAddon
 * @package Xpressengine\Plugins\Board\Extensions
 */
abstract class AbstractAddon implements ComponentInterface
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

    public static function boot()
    {

    }

    public function insert(ItemEntity $doc)
    {
    }

    public function makeQuery()
    {
        return '';
    }
    public function viewRead(ItemEntity $doc)
    {
        return '';
    }
    public function viewForm(ItemEntity $doc)
    {
        return '';
    }
    public function viewList($paginate)
    {
        return '';
    }

    public static function getManageUri()
    {
    }

    /**
     * 확장 모듈의 설정 페이지 url
     *
     * @param ConfigEntity $config board config
     * @return string
     */
    public function getConfigUrl(ConfigEntity $config)
    {

    }

    /**
     * 확장 모듈 사용으로 설정 할 경우 실행
     *
     * @param ConfigEntity $config board config
     * @return void
     */
    public function activate(ConfigEntity $config)
    {

    }
}
