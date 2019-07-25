<?php
/**
 * Validator
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
namespace Xpressengine\Plugins\Board;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\User\Models\Guest;
use Xpressengine\DynamicField\DynamicFieldHandler;
use Xpressengine\User\UserInterface;

/**
 * Validator
 *
 * 게시판에서 validate 에 사용하는 rule 을 case 별로 제공
 * 설정된 다이나믹 필드의 rule 처리
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class Validator
{

    /**
     * @var ConfigHandler
     */
    protected $configHandler;

    /**
     * @var DynamicFieldHandler
     */
    protected $register;

    /**
     * create instance
     *
     * @param ConfigHandler       $configHandler config handler
     * @param DynamicFieldHandler $dynamicField  plugin register manager
     */
    public function __construct(
        ConfigHandler $configHandler,
        DynamicFieldHandler $dynamicField
    ) {
        $this->configHandler = $configHandler;
        $this->dynamicField = $dynamicField;
    }

    /**
     * get create rule
     *
     * @param UserInterface $user   user
     * @param ConfigEntity  $config board config entity
     * @param array|null    $rules  rules
     * @return array
     */
    public function getCreateRule(UserInterface $user, ConfigEntity $config, array $rules = null)
    {
        $rules = $this->makeRule($config, $rules);
        if ($user instanceof Guest) {
            $rules = array_merge($rules, $this->guestStore());
        }

        return $rules;
    }

    /**
     * get edit rule
     *
     * @param UserInterface $user   user
     * @param ConfigEntity  $config board config entity
     * @param array|null    $rules  urles
     * @return array
     */
    public function getEditRule(UserInterface $user, ConfigEntity $config, array $rules = null)
    {
        $rules = $this->makeRule($config, $rules);
        if ($user instanceof Guest) {
            $rules = array_merge($rules, $this->guestUpdate());
        }

        return $rules;
    }

    /**
     * get guest certify rule
     *
     * @return array
     */
    public function guestCertifyRule()
    {
        return [
            'email' => 'Required|Email',
            'certify_key' => 'Required',
        ];
    }

    /**
     * 전달된 rule 에 다이나믹필드 의 rule 을 추가해서 반환
     *
     * @param ConfigEntity $config board config entity
     * @param array        $rules  rules
     * @return array
     */
    public function makeRule(ConfigEntity $config, array $rules = null)
    {
        if ($rules === null) {
            $rules = $this->basic();
        }

        if ($config->get('category') === true) {
            $rules = array_merge($rules, $this->category());
        }

        // add dynamic field rule
        /** @var \Xpressengine\Config\ConfigEntity $dynamicFieldConfig */
        foreach ($this->configHandler->getDynamicFields($config) as $dynamicFieldConfig) {
            $group = $dynamicFieldConfig->get('group');
            $id = $dynamicFieldConfig->get('id');
            $dynamicField = $this->dynamicField->get($group, $id);

            $rules = array_merge($rules, $dynamicField->getRules());
        }

        return $rules;
    }

    /**
     * 비회원 글 생성 규칙
     *
     * @return array
     */
    public function guestStore()
    {
        return [
            'writer' => 'Required|Min:2',
            'email' => 'Required|Between:3,64|Email',
            'certify_key' => 'Required|Between:4,64',
            'slug' => 'Required',
        ];
    }

    /**
     * 비회원 글 수정 규칙
     *
     * @return array
     */
    public function guestUpdate()
    {
        return [
            'writer' => 'Required|Min:2',
            'email' => 'Required|Between:3,64|Email',
            'certify_key' => 'Between:4,64',
        ];
    }

    /**
     * 글 생성 기본 규칙
     *
     * @return array
     */
    public function basic()
    {
        return [
            'title' => 'Required',
            'slug' => 'Required',
            'content' => 'Required',
        ];
    }

    /**
     * get rule for category
     *
     * @return array
     */
    public function category()
    {
        return [
            'category_item_id' =>  'Required',
        ];
    }
}
