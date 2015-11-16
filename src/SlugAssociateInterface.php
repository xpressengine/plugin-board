<?php
/**
 * SlugAssociateInterface
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

/**
 * SlugAssociateInterface
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
interface SlugAssociateInterface
{
    /**
     * get document id
     *
     * @return string
     */
    public function getDocumentId();

    /**
     * get instance id
     *
     * @return string
     */
    public function getInstanceId();

    /**
     * set slug entity
     *
     * @param SlugEntity $entity slug entity
     *
     * @return void
     */
    public function setSlugEntity(SlugEntity $entity);
}
