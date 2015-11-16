<?php
/**
 * Board handler
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

use Xpressengine\Comment\CommentHandler;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Member\Entities\Guest;
use Xpressengine\Member\Entities\MemberEntityInterface;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Document\DocumentEntity;
use Xpressengine\Interception\Proxy;
use Illuminate\Session\SessionManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Xpressengine\Plugins\Board\Exceptions\InvalidConfigException;
use Xpressengine\Plugins\ShortIdGenerator\Plugin as ShortIdGenerator;
use Xpressengine\Storage\Storage;
use Xpressengine\Member\Repositories\MemberRepositoryInterface as Member;
use Xpressengine\Member\GuardInterface as Authenticator;

/**
 * Board handler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Handler
{
    /**
     * Handler 에 주입 한 Board config entity
     * $this::setConfig() 로 주입
     *
     * @var ConfigEntity
     */
    protected $config;

    /**
     * board instance id
     *
     * @var string
     */
    protected $instanceId;

    /**
     * @var DocumentHandler
     */
    protected $document;

    /**
     * @var ShortIdGenerator
     */
    protected $shortIdGenerator;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var Member
     */
    protected $member;

    /**
     * @var Authenticator
     */
    protected $auth;

    /**
     * create instance
     *
     * @param DocumentHandler  $document         document handler(Interception Proxy)
     * @param ShortIdGenerator $shortIdGenerator short id generator
     * @param Storage          $storage          storage
     * @param SlugRepository   $slug             slug repository
     * @param Member           $member           member
     * @param Authenticator    $auth             auth
     */
    public function __construct(
        DocumentHandler $document,
        ShortIdGenerator $shortIdGenerator,
        Storage $storage,
        SlugRepository $slug,
        Member $member,
        Authenticator $auth
    ) {
        $this->document = $document;
        $this->shortIdGenerator = $shortIdGenerator;
        $this->storage = $storage;
        $this->slug = $slug;
        $this->member = $member;
        $this->auth = $auth;
    }

    /**
     * set this handler's board config
     *
     * @param ConfigEntity $config board config entity
     * @return void
     */
    public function setConfig(ConfigEntity $config)
    {
        $this->config = $config;
        $this->setInstancedId($config->get('boardId'));
    }

    /**
     * set this handler's board id
     *
     * @param string $instanceId board id
     * @return void
     */
    public function setInstancedId($instanceId)
    {
        $this->instanceId = $instanceId;
    }

    /**
     * get document count by board instance id
     *
     * @param string $boardId board id
     * @return int
     */
    public function countByBoardId($boardId)
    {
        return $this->document->countByInstanceId($boardId);
    }

    /**
     * get document list
     *
     * @param array $wheres 검색 조건
     * @param array $orders 정렬 조건
     * @param int   $limit  get document count
     * @return array
     */
    public function gets(array $wheres, array $orders, $limit = null)
    {
        $items = [];
        foreach ($this->document->gets($wheres, $orders, $limit) as $doc) {
            $items[] = $this->makeItem($doc);
        }

        $this->shortIdGenerator->associates($items);
        $this->slug->associates($items);

        return $items;
    }

    /**
     * get document list
     *
     * @param array $wheres 검색 조건
     * @param array $orders 정렬 조건
     * @param int   $limit  get document count
     * @return array
     */
    public function getsNotice(array $wheres = [], array $orders = [], $limit = null)
    {
        if (empty($wheres['status'])) {
            $wheres['status'] = DocumentEntity::STATUS_NOTICE;
        }
        if (empty($wheres['display'])) {
            $wheres['display'] = DocumentEntity::DISPLAY_VISIBLE;
        }
        if (empty($wheres['published'])) {
            $wheres['published'] = DocumentEntity::PUBLISHED_PUBLISHED;
        }

        if (count($orders) == 0) {
            $orders = ['createdAt' => 'desc'];
        }

        return $this->gets($wheres, $orders, $limit);
    }

    /**
     * get document list by paginate
     * board item entity class list
     *
     * @param array        $wheres 검색 조건
     * @param array        $orders 정렬 조건
     * @param ConfigEntity $config board config entity
     * @return LengthAwarePaginator
     * @see \Xpressengine\Document\Repositories\DocumentRepository
     */
    public function paginate(array $wheres, array $orders, ConfigEntity $config = null)
    {
        if ($config == null) {
            $config = $this->config;
        }

        if ($orders == [] && $config->get('orderExtension') != null) {
            $orderType = sprintf('\\%s', $config->get('orderExtension'));
            (new $orderType)->make($wheres, $orders);
        }

        $paginator = $this->document->paginate($wheres, $orders, $config->get('perPage'));

        // wrap item entity
        foreach ($paginator as $key => $doc) {
            $paginator[$key] = $this->makeItem($doc);
        }

        $this->shortIdGenerator->associates($paginator);
        $this->slug->associates($paginator);

        return $paginator;
    }

    /**
     * $params 에서 Document entity 를 구성할 값을 필터링
     * * underscore 로 시작하는 이름 제거
     * * array 제거
     *
     * @param array $params parameters
     * @return array
     */
    public function documentFilter(array $params)
    {
        $items = [];
        foreach ($params as $key => $value) {
            if (substr($key, 1) == '_') {
                continue;
            }
            if (in_array($key, ['certifyKey_confirmation', 'anonymity', 'queryString', 'notice'])) {
                continue;
            }
            if (is_array($value) || is_object($value)) {
                continue;
            }

            $items[$key] = $value;
        }

        return $items;
    }

    /**
     * create board item entity by document entity
     *
     * @param DocumentEntity        $doc     document entity
     * @param Request               $request request
     * @param MemberEntityInterface $user    user
     * @return ItemEntity
     */
    public function makeItem(DocumentEntity $doc, Request $request = null, MemberEntityInterface $user = null)
    {
        if ($request !== null && $user !== null) {
            if ($request->get('notice') === '1') {
                $doc->notice();
            }

            // 비회원 글쓰기 또는 익명 글쓰기 처리
            if ($user instanceof Guest) {
                $doc->guest();
            } elseif ($request->get('anonymity') == '1') {
                $doc->anonymity($this->config->get('anonymityName'));
            } else {
                $doc->setAuthor($user);
            }
        }

        $item = new ItemEntity();
        $item->setDocument($doc);

        if ($request !== null) {
            // set files
            if (($fileIds = $request->get('_files')) !== null) {
                $item->setFiles($this->storage->getsIn($fileIds));
            }
        }
        return $item;
    }

    /**
     * get document
     *
     * @param string $id         document id
     * @param string $instanceId instance id
     * @return ItemEntity
     */
    public function get($id, $instanceId = null)
    {
        $doc = $this->document->get($id, $instanceId);
        if ($doc->userId === '') {
            $doc->setAuthor($this->auth->makeGuest());
        } else {
            $doc->setAuthor($this->member->find($doc->getUserId()));
        }

        $entity = $this->makeItem($doc);

        $this->shortIdGenerator->associate($entity);
        $this->slug->associate($entity);

        return $entity;
    }

    /**
     * insert a document
     *
     * @param ItemEntity   $item   board item entity
     * @param ConfigEntity $config board config entity
     * @return void
     */
    public function add(ItemEntity $item, ConfigEntity $config)
    {
        $doc = $item->getDocument();

        // board 의 function type 을 가져와 insert 실행
//        if ($functionTypes = $config->get('functionType')) {
//            foreach ($functionTypes as $className) {
//                (new $className)->insert($doc);
//            }
//        }

        $this->document->getRepository()->connection()->beginTransaction();

        // board 의 설정을 가져와서.. 어떤 글인가... 확인해야 겠다...
        // 그래서 어떤 insert 를 사용할 결정해야 겠어.
        $this->document->add($doc);

        $this->shortIdGenerator->make($item->id);

        $slugEntity = new SlugEntity;
        $slugEntity->slug = $item->slug;
        $slugEntity->title = $item->title;
        $slugEntity->id = $item->id;
        $slugEntity->instanceId = $item->instanceId;
        $this->slug->insert($slugEntity);

        // interception 사용?
        // Todo 게시판에 files 라고 있는데.. 이게 업로드된 파일 id 정보이다.
        // 이걸 doc->id 와 매핑 시켜줘야 한다.
        /** @var \Xpressengine\Storage\File $file */
        foreach ($item->getFiles() as $file) {
            $this->storage->bind($doc->id, $file);
        }

        $this->document->getRepository()->connection()->commit();
    }

    /**
     * update document
     *
     * @param ItemEntity $item board item entity
     * @return void
     */
    public function put(ItemEntity $item)
    {
        $doc = $item->getDocument();

        // 비회원 글 수정시 비밀번호를 입력 안한 경우
        $origin = $doc->getOriginal();
        if ($origin['certifyKey'] != '' && $doc->certifyKey == '') {
            $doc->certifyKey = $origin['certifyKey'];
        }

        $doc = $this->document->put($doc);

        if ($item->getSlug() != null) {
            $this->slug->update($item->getSlug());
        }

        // file 이 없어진걸 처리해야해.. 파일을 삭제한 경우를 말이지!
        $currentFileIds = [];
        /** @var \Xpressengine\Storage\File $file */
        foreach ($this->storage->getsByTargetId($item->id) as $file) {
            $currentFileIds[] = $file->getId();
        }

        $uploadedFileIds = [];
        /** @var \Xpressengine\Storage\File $file */
        foreach ($item->getFiles() as $file) {
            $uploadedFileIds[] = $file->getId();
            $this->storage->bind($doc->id, $file);
        }

        $files = $this->storage->getsIn(array_diff($currentFileIds, $uploadedFileIds));
        foreach ($files as $file) {
            $this->storage->unBind($item->id, $file, true);
        }
    }

    /**
     * 문서 삭제
     *
     * @param ItemEntity     $item   board item entity
     * @param ConfigEntity   $config destination board config entity
     * @return int
     */
    public function remove(ItemEntity $item, ConfigEntity $config)
    {
        $item = $this->get($item->id, $item->instanceId);

        // 덧글이 있다면 덧글들을 모두 휴지통으로 옯긴다.
        $count = 0;
        if ($config->get('recursiveDelete') === true) {
            $rows = $this->document->getRepository()->getReplies($item->getDocument());
            foreach ($rows as $row) {
                $item = $this->get($row['id'], $row['instanceId']);

                $count = $this->document->remove($item->getDocument());
                $this->slug->delete($item->getSlug());
                /** @var \Xpressengine\Storage\File $file */
                foreach ($this->storage->getsByTargetId($item->id) as $file) {
                    $this->storage->unBind($item->id, $file, true);
                }
            }
        } else {
            $count = $this->document->remove($item->getDocument());
            $this->slug->delete($item->getSlug());
            /** @var \Xpressengine\Storage\File $file */
            foreach ($this->storage->getsByTargetId($item->id) as $file) {
                $this->storage->unBind($item->id, $file, true);
            }
        }

        return $count;
    }

    /**
     * 문서 휴지통 이동
     *
     * @param ItemEntity     $item   board item entity
     * @param ConfigEntity   $config destination board config entity
     * @return void
     */
    public function trash(ItemEntity $item, ConfigEntity $config)
    {
        $item = $this->get($item->id, $item->instanceId);

        // 덧글이 있다면 덧글들을 모두 휴지통으로 옯긴다.
        if ($config->get('recursiveDelete') === true) {
            $rows = $this->document->getRepository()->getReplies($item->getDocument());
            foreach ($rows as $row) {
                $doc = new DocumentEntity($row);
                $doc->trash();
                $item = $this->makeItem($doc);

                if ($item->userId == '') {
                    $item->userId = '';
                }
                $this->put($item);
            }
        } else {
            $this->document->trash($item->getDocument());
        }
    }

    /**
     * 문서 복원
     *
     * @param ItemEntity $item board item entity
     * @return int 삭제된 문서 수
     */
    public function restore(ItemEntity $item)
    {

        return $this->document->restore($item->getDocument());
    }

    /**
     * 게시판 이동
     * Document Package 에서 comment 를 지원하지 않아서 사용할 수 있는 인터페이스가 없음
     *
     * @param string         $id             document id
     * @param ConfigEntity   $config         destination board config entity
     * @param CommentHandler $commentHandler comment handler
     */
    public function move($id, ConfigEntity $config, CommentHandler $commentHandler)
    {
        if ($config === null) {
            throw new InvalidConfigException;
        }

        $item = $this->get($id);
        $doc = $item->getDocument();

        $dstInstanceId = $config->get('boardId');

        // 덧글이 있다면 덧글들을 모두 옯긴다.
        // 이거 document 인터페이스 있으멶 좋을까?? 사용 할 일 없어 보이는데.
        $rows = $this->document->getRepository()->getReplies($doc);
        foreach ($rows as $row) {
            $item = $this->get($row['id'], $row['instanceId']);
            $item->instanceId = $dstInstanceId;
            $item->getSlug()->instanceId = $dstInstanceId;

            if ($item->userId == '') {
                $item->userId = '';
            }
            $this->put($item);
            $commentHandler->moveByTarget($dstInstanceId, $item->id);
        }
    }

    /**
     * 복사
     *
     * @param string       $id     document id
     * @param ConfigEntity $config destination board config entity
     * @param string       $newId  new document id
     * @return void
     */
    public function copy($id, ConfigEntity $config, $newId)
    {
        if ($config === null) {
            throw new InvalidConfigException;
        }

        $item = $this->get($id);
        $item->id = $newId;
        $item->parentId = '';
        $item->instanceId = $config->get('boardId');
        $item->slug = $item->getSlug()->slug;

        $doc = $item->getDocument();
        $item = $this->makeItem($doc);

        $this->add($item, $config);
    }
}
