<?php
/**
 * SlugAssociateInterface
 *
 * PHP version 5
 *
 * @category    Slug
 * @package     Xpressengine\Plugins\Slug
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Slug;

/**
 * SlugAssociateInterface
 *
 * @category    Slug
 * @package     Xpressengine\Plugins\Slug
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
     * set slug instance
     * entity 에다가. 이걸 넣는게 맞는거겠냐?
     * @param Slug $instance slug
     * @return void
     */
    public function setSlugEntity(Slug $instance);
}
