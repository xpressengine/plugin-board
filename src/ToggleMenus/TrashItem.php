<?php
namespace Xpressengine\Plugins\Board\ToggleMenus;

use Xpressengine\ToggleMenu\AbstractToggleMenu;

class TrashItem extends AbstractToggleMenu
{
    public static $id = 'module/board@board/toggleMenu/xpressengine@trashItem';

    protected $type;
    protected $docuemntId;

    public function __construct($type, $docuemntId)
    {
        $this->type = $type;
        $this->docuemntId = $docuemntId;
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
        $doc = app('xe.document')->getById($this->docuemntId);

        $config = app('xe.board.config')->get($doc->getInstanceId());

        return app('xe.board.url')->get('trash', ['id' => $this->docuemntId], $config);
    }

    public function getScript()
    {
    }

    public function getIcon()
    {
        return null;
    }
}