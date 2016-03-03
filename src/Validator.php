<?php
/**
 * Validator
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\User\UserInterface;
use Xpressengine\User\Models\Guest;
use Xpressengine\DynamicField\DynamicFieldHandler;


/**
 * Validator
 *
 * * 게시판에서 validate 에 사용하는 rule 을 case 별로 제공
 * * 설정된 다이나믹 필드의 rule 처리
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
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
     * @param ConfigEntity          $config board config entity
     * @param array|null            $rules  rules
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

    public function getEditRule(UserInterface $user, ConfigEntity $config, array $rules = null)
    {
        $rules = $this->makeRule($config, $rules);
        if ($user instanceof Guest) {
            $rules = array_merge($rules, $this->guestUpdate());
        }

        return $rules;
    }

    /**
     * 전달된 rule 에 다이나믹필드 의 rule 을 추가해서 반환
     *
     * @param ConfigEntity $config board config entity
     * @param array        $rules  rule
     * @return array
     */
    public function makeRule(ConfigEntity $config, array $rules = null)
    {
        if ($rules === null) {
            $rules = $this->basic();
        }

        // add dynamic field rule
        /** @var \Xpressengine\Config\ConfigEntity $dynamicFieldConfig */
        foreach ($this->configHandler->getDynamicFields($config) as $dynamicFieldConfig) {
            /** @var \Xpressengine\DynamicField\AbstractType $type */
            $rules = array_merge($rules, $this->dynamicField->getRules($dynamicFieldConfig));
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
            'writer' => 'Required|AlphaNum|Min:4',
            'email' => 'Required|Between:3,64|Email',
            'certifyKey' => 'Required|AlphaNum|Between:4,64|',
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
            'writer' => 'Required|AlphaNum|Min:4',
            'email' => 'Required|Between:3,64|Email',
            'certifyKey' => 'AlphaNum|Between:4,64|',
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
}
