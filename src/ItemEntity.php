<?php
/**
 * ItemEntity
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

use Xpressengine\Member\Entities\Guest;
use Xpressengine\Seo\SeoUsable;
use Xpressengine\Support\EntityTrait;
use Xpressengine\Document\DocumentEntity;
use Xpressengine\Storage\File;
use Xpressengine\Plugins\CommentService\CommentUsable;
use Xpressengine\Member\Entities\MemberEntityInterface;
use Xpressengine\Plugins\ShortIdGenerator\AssociateInterface as ShortIdAssociateInterface;
use Xpressengine\Plugins\ShortIdGenerator\ItemEntity as ShortIdEntity;

/**
 * ItemEntity
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 *
 * @property string $id             고유한 식별자
 * @property string $parentId       parent id
 * @property string $instanceId     instance id
 * @property string $content        내용
 * @property string $pureContent    특수문자, html 태그가 제거된 내용
 * @property string $title          제목
 */
class ItemEntity implements ShortIdAssociateInterface, CommentUsable, SlugAssociateInterface, SeoUsable
{
    use EntityTrait;

    /**
     * @var DocumentEntity
     */
    protected $doc;

    /**
     * @var []
     */
    protected $slug;

    /**
     * @var ShortIdEntity
     */
    protected $shortId;

    /**
     * File lists
     *
     * @var array
     */
    protected $files = [];

    /**
     * set document entity
     *
     * @param DocumentEntity $doc document entity
     * @return void
     */
    public function setDocument(DocumentEntity $doc)
    {
        $this->doc = $doc;

        // ItemEntity 는 document entity 의 attributes 를 갖는다.
        $this->attributes = $doc->toArray();
        if ($this->original === []) {
            $this->original = $this->attributes;
        }
    }

    /**
     * get document entity
     *
     * @return DocumentEntity
     */
    public function getDocument()
    {
        // item entity 의 attributes 를 document entity 에 dump

        $this->doc->fill($this->getAttributes());
        return $this->doc;
    }

    /**
     * set files
     *
     * @param File $file file entity
     * @return void
     */
    public function setFile(File $file)
    {
        $this->files[$file->getId()] = $file;
    }

    /**
     * set files
     *
     * @param File[] $files file entity list
     * @return void
     */
    public function setFiles(array $files)
    {
        /** @var \Xpressengine\Storage\File $file */
        foreach ($files as $file) {
            $this->setFile($file);
        }
        $this->__set('fileCount', count($this->files));
        $this->getDocument();
    }

    /**
     * get file entity
     *
     * @param string $id file id
     * @return File
     */
    public function getFile($id)
    {
        return $this->files[$id];
    }

    /**
     * get file entity list
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * get short id entity
     *
     * @return ShortIdEntity
     */
    public function getShortId()
    {
        return $this->shortId;
    }

    /**
     * set short id entity
     *
     * @param ShortIdEntity $shortId short id entity
     * @return mixed
     */
    public function setShortIdEntity(ShortIdEntity $shortId)
    {
        $this->shortId = $shortId;
    }

    /**
     * Returns unique identifier
     *
     * @return string
     */
    public function getUid()
    {
        return $this->__get('id');
    }

    /**
     * Returns instance identifier
     *
     * @return string
     */
    public function getInstanceId()
    {
        return $this->__get('instanceId');
    }

    /**
     * Returns author
     *
     * @return MemberEntityInterface
     */
    public function getAuthor()
    {
        return $this->doc ? $this->doc->getAuthor() : new Guest();
    }

    /**
     * get slug
     *
     * @return SlugEntity
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * 슬러그 사용 유무
     *
     * @return bool
     */
    public function slug()
    {
        return $this->__get('useSlug') === null ? false : $this->__get('useSlug');
    }

    /**
     * get document id
     *
     * @return string
     */
    public function getDocumentId()
    {
        return $this->id;
    }

    /**
     * set slug entity
     *
     * @param SlugEntity $entity slug entity
     *
     * @return void
     */
    public function setSlugEntity(SlugEntity $entity)
    {
        $this->slug = $entity;
    }

    /**
     * Returns title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->doc ? $this->doc->getPureContent() : '';
    }

    /**
     * Returns keyword
     *
     * @return string|array
     */
    public function getKeyword()
    {
        return isset($this->tags) ? $this->tags : [];
    }

    /**
     * Returns url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->permalink;
    }

    /**
     * Returns image url list
     *
     * @return array
     */
    public function getImages()
    {
        return $this->images ?: [];
    }

    /**
     * call document entity method
     *
     * @param string $method     method name
     * @param array  $parameters parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array(array($this->getDocument(), $method), $parameters);
    }
}
