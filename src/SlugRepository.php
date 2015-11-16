<?php
/**
 * Board rules
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

use Xpressengine\Database\VirtualConnectionInterface;

/**
 * Board rules
 * 게시판에서 validate 에 사용하는 rule 을 case 별로 제공
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class SlugRepository
{

    /**
     * slug 로 사용할 수 없는 예약어
     *
     * @var array
     */
    protected static $reserved = [];

        /**
     * table 이름
     *
     * @var string
     */
    protected $table = 'slug';

    /**
     * Database connection
     *
     * @var VirtualConnectionInterface
     */
    protected $connection;

    /**
     * Memory cache
     *
     * @var SlugEntity[]
     */
    protected $slugs = [];

    /**
     * create instance
     *
     * @param VirtualConnectionInterface $connection database connection
     */
    public function __construct(VirtualConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

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
    public function convert($title, $slug = null)
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
     * make slug string
     *
     * @param string $slug slug
     * @param string $id   document id
     * @return string
     */
    public function make($slug, $id)
    {
        $slug = $this->convert($slug);

        $increment = 0;
        if (in_array($slug, self::$reserved) === true) {
            ++$increment;
        }

        while ($this->has($slug, $increment) === true) {
            $slugInfo = $this->find($slug);
            if ($slugInfo->id == $id) {
                break;
            }

            ++$increment;
        }

        return $this->makeIncrement($slug, $increment);
    }

    /**
     * 새로운 문자 생성
     *
     * @param string $slug      slug
     * @param int    $increment increment count
     * @return string
     */
    protected function makeIncrement($slug, $increment)
    {
        if ($increment > 0) {
            $slug = $slug . '-' . $increment;
        }
        return $slug;
    }

    /**
     * has slug
     *
     * @param string $slug      slug
     * @param int    $increment increment count
     * @return int
     */
    public function has($slug, $increment = 0)
    {
        $slug = $this->makeIncrement($slug, $increment);

        $query = $this->connection->table($this->table)->where('slug', $slug);
        $count = $query->count();
        if ($count !== 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * insert document
     *
     * @param SlugEntity $slugEntity slug entity
     * @return SlugEntity
     */
    public function insert(SlugEntity $slugEntity)
    {
        $slug = $this->make($slugEntity->slug, $slugEntity->id);

        $slugEntity->slug = $slug;
        $this->connection->table($this->table)
            ->insert([
                'slug' => $slug,
                'id' => $slugEntity->id,
                'instanceId' => $slugEntity->instanceId,
                'title' => $slugEntity->title,
            ]);
        return $slugEntity;
    }

    /**
     * update document by $wheres
     *
     * @param SlugEntity $slugEntity slug entity
     * @return SlugEntity
     */
    public function update(SlugEntity $slugEntity)
    {
        $this->removeCache($slugEntity);

        $slug = $this->make($slugEntity->slug, $slugEntity->id);

        $slugEntity->slug = $slug;
        $this->connection->table($this->table)
            ->where('id', $slugEntity->id)->update([
                'slug' => $slugEntity->slug,
                'instanceId' => $slugEntity->instanceId,
                'title' => $slugEntity->title,
            ]);
        return $slugEntity;
    }

    /**
     * 게시물 이동 시 instance id 변경
     *
     * @param SlugEntity $slugEntity slug entity
     * @return SlugEntity
     */
    public function updateInstanceId(SlugEntity $slugEntity)
    {
        $this->removeCache($slugEntity);
        $this->connection->table($this->table)
            ->where('id', $slugEntity->id)->update([
                'instanceId' => $slugEntity->instanceId,
            ]);
        return $slugEntity;
    }

    /**
     * delete document
     *
     * @param SlugEntity $slugEntity slug entity
     * @return int
     */
    public function delete(SlugEntity $slugEntity)
    {
        $this->removeCache($slugEntity);
        return $this->connection->table($this->table)
            ->where(['id'=>$slugEntity->id])->delete();
    }

    /**
     * find slug
     *
     * @param string $slug slug
     * @return SlugEntity|null
     */
    public function find($slug)
    {
        $query = $this->connection->table($this->table)->where('slug', $slug);
        $row = $query->first();
        $slugEntity = null;
        if ($row != null) {
            $slugEntity = new SlugEntity($row);
            $this->putCache($slugEntity);
        }
        return $slugEntity;
    }

    /**
     * find slug by id
     *
     * @param string $id         id
     * @param string $instanceId board instance id
     * @return SlugEntity
     */
    public function findById($id, $instanceId)
    {
        if ($this->hasCache($id, $instanceId) === true) {
            return $this->getCache($id, $instanceId);
        }
        $row = $this->connection->table($this->table)
            ->where('id', $id)->where('instanceId', $instanceId)->first();

        $slug = null;
        if ($row != null) {
            $slug = new SlugEntity($row);
            $this->putCache($slug);
        }
        return $slug;
    }

    /**
     * fetch slugs
     *
     * @param string|array $ids         ids
     * @param string|array $instanceIds instance ids
     * @return SlugEntity[]
     */
    public function fetchByIdsInstanceIds($ids, $instanceIds)
    {
        $slugs = $this->connection->table($this->table)
            ->whereIn('id', $ids)->whereIn('instanceId', $instanceIds)->get();

        foreach ($slugs as $key => $row) {
            $slugs[$key] = new SlugEntity($row);
            $this->putCache($slugs[$key]);
        }

        return $slugs;
    }

    /**
     * add memory cache
     *
     * @param SlugEntity $slug slug entity
     * @return void
     */
    private function putCache(SlugEntity $slug)
    {
        if (empty($this->slugs[$slug->instanceId])) {
            $this->slugs[$slug->instanceId] = [];
        }
        $this->slugs[$slug->instanceId][$slug->id] = $slug;
    }

    /**
     * remove memory cache
     *
     * @param SlugEntity $slug slug entity
     * @return void
     */
    private function removeCache(SlugEntity $slug)
    {
        if ($this->getCache($slug->id, $slug->instanceId) !== null) {
            unset($this->slugs[$slug->instanceId][$slug->id]);
        }
    }

    /**
     * get memory cache
     *
     * @param string $id         document id
     * @param string $instanceId instance id
     * @return SlugEntity|null
     */
    private function getCache($id, $instanceId)
    {
        return $this->hasCache($id, $instanceId) === true ? $this->slugs[$instanceId][$id] : null;
    }

    /**
     * has memory cache
     *
     * @param string $id         document id
     * @param string $instanceId instance id
     * @return bool
     */
    private function hasCache($id, $instanceId)
    {
        return empty($this->slugs[$instanceId][$id]) === false ? true : false;
    }
}
