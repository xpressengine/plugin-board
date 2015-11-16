<?php
/**
 * SlugEntity
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

use Xpressengine\Support\EntityTrait;

/**
 * SlugEntity

 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 *
 * @property string $slug       slug
 * @property string $id         document id
 * @property string $instanceId document instance id
 * @property string $title      document origin title
 */
class SlugEntity
{
    use EntityTrait;

    /**
     * short generated id
     *
     * @return string
     */
    public function getId()
    {
        return $this->__get('id');
    }

    /**
     * original id
     *
     * @return string
     */
    public function getOriginId()
    {
        return $this->__get('originId');
    }
}
