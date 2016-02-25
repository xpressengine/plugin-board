<?php
/**
 * TrashItem
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
namespace Xpressengine\Plugins\Board\ToggleMenus;

use Xpressengine\ToggleMenu\AbstractToggleMenu;

/**
 * TrashItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class TrashItem extends AbstractToggleMenu
{
    public static $id = 'module/board@board/toggleMenu/xpressengine@trashItem';

    protected $type;

    protected $documentId;

    public function __construct($type, $documentId)
    {
        $this->type = $type;
        $this->documentId = $documentId;
    }

    public static function getName()
    {
        return '휴지통';
    }

    public static function getDescription()
    {
        return '선택한 문서를 휴지통으로 보냅니다.';
    }

    public function getText()
    {
        return '휴지통';
    }

    public function getType()
    {
        return 'link';
    }

    public function getAction()
    {
        $doc = app('xe.document')->get($this->documentId);

        $config = app('xe.board.config')->get($doc->getInstanceId());

        return app('xe.board.url')->get('trash', ['id' => $this->documentId], $config);
    }

    public function getScript()
    {
    }

    public function getIcon()
    {
        return null;
    }
}