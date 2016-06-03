<?php
/**
 * TrashItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\ToggleMenus;

use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\ToggleMenu\AbstractToggleMenu;

/**
 * TrashItem
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Crop. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
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
        $doc = Board::find($this->documentId);

        $config = app('xe.board.config')->get($doc->instanceId);

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
