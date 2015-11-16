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
namespace Xpressengine\Plugins\Board\Addon;

use Xpressengine\Document\DocumentEntity;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugins\Board\ItemEntity;

/**
 * Extension
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class CounselBoard extends AbstractAddon
{
    /**
     * @var string
     */
    protected static $id = 'module/board@board/addon/board@CounselBoard';

    /**
     * get id
     * Register Container 에 사용 될 key
     *
     * @return string
     * @see \Xpressengine\Register\Container
     */
    public static function getId()
    {
        return self::$id;
    }

    /**
     *
     * @return void
     */
    public static function boot()
    {
        // TODO: Implement boot() method.
    }

    /**
     * get name
     *
     * @return string
     */
    public function name()
    {
        return '상담 기능';
    }

    /**
     * get description
     *
     * @return string
     */
    public function description()
    {
        return '상담 기능은 관리권한이 없는 회원은 자신이 쓴 글만 보이도록 하는 기능입니다. 단 상담기능 사용시 비회원 글쓰기는 자동으로 금지됩니다.';
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
