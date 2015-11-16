<?php
/**
 * Revision handler
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

use Xpressengine\Config\ConfigManager;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\DynamicField\ConfigHandler as DynamicFieldConfigHandler;
use Xpressengine\Document\ConfigHandler as DocumentConfigHandler;

/**
 * Revision handler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class RevisionHandler
{

    /**
     * @var DocumentHandler
     */
    protected $document;

    /**
     * @var ConfigHandler
     */
    protected $configHandler;

    public function __construct(DocumentHandler $document, ConfigHandler $configHandler)
    {
        $this->document = $document;
        $this->configHandler = $configHandler;
    }

    /**
     * get revision list by document id
     *
     * @param string $id document id
     * @return array
     */
    public function getRevisions($id)
    {
        return $this->document->getRevisions($id);
    }

    /**
     * get revision document entity
     *
     * @param string $revisionId revision id
     * @return DocumentEntity
     */
    public function getRevision($revisionId)
    {
        return $this->document->getRevision(
            $revisionId,
            $this->configHandler->getDynamicFields($this->config->get('boardId'))
        );
    }
}
