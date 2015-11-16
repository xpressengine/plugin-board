<?php
/**
 * Slug
 *
 * PHP version 5
 *
 * @category    Slug
 * @package     Xpressengine\Plugins\Board\Slug
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Slug;

use Xpressengine\Database\Eloquent\DynamicModel;

/**
 * Slug

 * @category    Slug
 * @package     Xpressengine\Plugins\Board\Slug
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
class Slug extends DynamicModel
{
    protected $table = 'slug';

    /**
     * 예약어 추가
     * * 게시판 Routing 에 사용한는 이름은 슬러그로 사용할 수 없음
     *
     * @param string|array $slug slug
     * @return void
     */
    public static function setReserved($slug)
    {
        if (is_array($slug) === true) {
            self::$reserved = array_merge(self::$reserved, $slug);
        } else {
            self::$reserved[] = $slug;
        }

    }

    /**
     * associate
     *
     * @param SlugAssociateInterface $entity slug associate interface entity
     * @return SlugAssociateInterface
     */
    public function associate(SlugAssociateInterface $entity)
    {
        $instanceId = $entity->getInstanceId();
        $documentId = $entity->getDocumentId();
        $slug = $this->findById($documentId, $instanceId);
        if ($slug != null) {
            $entity->setSlugEntity($slug);
        }
        return $entity;
    }

    /**
     * associates
     *
     * @param SlugAssociateInterface[] $entities slug associate interface entities or paginator
     * @return SlugAssociateInterface[]
     */
    public function associates($entities)
    {
        $entitiesByInstanceId = [];
        $instanceIds = [];
        $documentIds = [];
        foreach ($entities as $entity) {
            $instanceId = $entity->getInstanceId();
            if (empty($entitiesByInstanceId[$instanceId])) {
                $entitiesByInstanceId[$instanceId] = [];
            }

            $documentId = $entity->getDocumentId();
            $entitiesByInstanceId[$instanceId][$documentId] = $entity;
            $documentIds[] = $documentId;
            $instanceIds[] = $instanceId;
        }

        $slugs = $this->fetchByIdsInstanceIds(array_unique($documentIds), array_unique($instanceIds));

        /** @var \Xpressengine\Plugins\Board\SlugEntity $entity */
        foreach ($slugs as $entity) {
            $instanceId = $entity->instanceId;
            $id = $entity->id;
            $entitiesByInstanceId[$instanceId][$id]->setSlugEntity($entity);
        }

        return $entities;
    }

    /**
     * convert title to slug
     * $title 을 ascii 코드로 변환 후 하이픈을 제외한 모든 특수문자 제거
     * 스페이스를 하이픈으로 변경
     *
     * @param string $title title
     * @param string $slug  slug
     * @return string
     */
    public function convert($title, $slug)
    {
        // $slug 가 있다면 넘겨받은 slug 로 convert
        if ($slug != null) {
            $title = $slug;
        }
        $title = trim($title);

        // space change to dash
        $title = str_replace(' ', '-', $title);

        $slug = '';
        $len = mb_strlen($title);
        for ($i=0; $i<$len; $i++) {
            $ch = mb_substr($title, $i, 1);
            $code = $this->utf8Ord($ch);
            if (
                ($code <= 47 && $code != 45) ||
                ($code >= 58 && $code <= 64) ||
                ($code >= 123 && $code <= 127)
            ) {
                continue;
            }
            $slug .= $ch;
        }

        // remove double dash
        $slug = str_replace('--', '-', $slug);

        return $slug;
    }

    /**
     * get ascii code
     *
     * @param string $ch character
     * @return bool|int
     */
    protected function utf8Ord($ch)
    {
        $len = strlen($ch);
        if ($len <= 0) {
            return false;
        }
        $h = ord($ch{0});
        if ($h <= 0x7F) {
            return $h;
        }
        if ($h < 0xC2) {
            return false;
        }
        if ($h <= 0xDF && $len>1) {
            return ($h & 0x1F) <<  6 | (ord($ch{1}) & 0x3F);
        }
        if ($h <= 0xEF && $len>2) {
            return ($h & 0x0F) << 12 | (ord($ch{1}) & 0x3F) << 6 | (ord($ch{2}) & 0x3F);
        }
        if ($h <= 0xF4 && $len>3) {
            return ($h & 0x0F) << 18 | (ord($ch{1}) & 0x3F) << 12 | (ord($ch{2}) & 0x3F) << 6 | (ord($ch{3}) & 0x3F);
        }
        return false;
    }

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
