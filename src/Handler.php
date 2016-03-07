<?php
/**
 * Board handler
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\Counter\Counter;
use Xpressengine\Database\Eloquent\Builder;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Models\BoardCategory;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Storage\File;
use Xpressengine\Storage\Storage;
use Xpressengine\Tag\Tag;
use Xpressengine\Tag\TagHandler;
use Xpressengine\User\Models\Guest;
use Xpressengine\User\UserInterface;


/**
 * Board handler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Handler
{

    /**
     * @var DocumentHandler
     */
    protected $documentHandler;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var TagHandler
     */
    protected $tag;

    protected $readCounter;

    protected $voteCounter;


    /**
     * create instance
     *
     * @param DocumentHandler  $documentHandler  document handler(Interception Proxy)
     * @param Storage          $storage          storage
     */
    public function __construct(
        DocumentHandler $documentHandler,
        Storage $storage,
        TagHandler $tag,
        Counter $readCounter,
        Counter $voteCounter
    ) {
        $this->documentHandler = $documentHandler;
        $this->storage = $storage;
        $this->tag = $tag;
        $this->readCounter = $readCounter;
        $this->voteCounter = $voteCounter;
    }

    public function getReadCounter()
    {
        return $this->readCounter;
    }

    public function getVoteCounter()
    {
        return $this->voteCounter;
    }

    /**
     * 게시판에 글 등록 시 핸들러를 통해서 처리
     * Interception 을 통해 다양한 서드파티 기능이 추가될 수 있다.
     *
     * @param array         $args arguments
     * @param UserInterface $user
     *
     * @return Board
     */
    public function add(array $args, UserInterface $user, ConfigEntity $config)
    {
        $model = $this->getModel($config);
        $model->getConnection()->beginTransaction();

        $args['userId'] = $user->getId();
        if ($args['userId'] === null) {
            $args['userId'] = '';
        }
        if (empty($args['writer'])) {
            $args['writer'] = $user->getDisplayName();
        }
        if ($user instanceof Guest) {
            $args['userType'] = Board::USER_TYPE_GUEST;
        }

        // save Document
        $doc = $this->documentHandler->add($args);

        $model = $this->getModel($config);
        $board = $model->find($doc->id);

        $this->setModelConfig($board, $config);

        // save Slug
        $slug = new BoardSlug([
            'slug' => $args['slug'],
            'title' => $args['title'],
            'instanceId' => $args['instanceId'],
        ]);
        $board->boardSlug()->save($slug);

        // save Category
        if (empty($args['categoryItemId']) == false) {
            $boardCategory = new BoardCategory([
                'id' => $doc->id,
                'itemId' => $args['categoryItemId'],
            ]);
            $boardCategory->save();
        }

        if (empty($args['_files']) === false) {
            foreach (File::whereIn('id', $args['_files'])->get() as $file) {
                $this->storage->bind($doc->id, $file);
            }
        }

        if (empty($args['_hashTags']) === false) {
            $this->tag->set($doc->id, $args['_hashTags'], $doc->instanceId);
        }

        $model->getConnection()->commit();

        return $board;
    }

    public function put(Board $board, array $args)
    {
        $board->getConnection()->beginTransaction();

        foreach ($args as $name => $value) {
            if ($board->{$name} !== null) {
                $board->{$name} = $value;
            }
        }

        $doc = $this->documentHandler->put($board);

        $boardSlug = $board->boardSlug;
        $boardSlug->slug = $args['slug'];
        $boardSlug->title = $board->title;
        $board->boardSlug()->save($boardSlug);

        // save Category
        if (empty($args['itemId']) == false) {
            $boardCategory = $board->boardCategory;
            if ($boardCategory == null) {
                $boardCategory = new BoardCategory([
                    'id' => $doc->id,
                    'itemId' => $args['itemId'],
                ]);
            } else {
                $boardCategory->itemId = $board->itemId;
            }

            $boardCategory->save();
        }

        // save Category
        if (empty($args['categoryItemId']) == false) {
            $boardCategory = $board->boardCategory;
            if ($boardCategory == null) {
                $boardCategory = new BoardCategory([
                    'id' => $doc->id,
                    'itemId' => $args['categoryItemId'],
                ]);
            } else {
                $boardCategory->itemId = $args['categoryItemId'];
            }
            $boardCategory->save();
        }

        // bind files
        // 업데이트 할 때 중복 bind 되어 fileable 이 계속 증가하는 오류가 있음
        $fileIds = [];
        if (empty($args['_files']) === false) {
            foreach (File::whereIn('id', $args['_files'])->get() as $file) {
                $fileIds[] = $file->id;
                if ($this->storage->has($doc->id, $file) === false) {
                    $this->storage->bind($doc->id, $file);
                }
            }
        }

        $files = File::whereIn('id', array_diff($board->getFileIds(), $fileIds))->get();
        foreach ($files as $file) {
            $this->storage->unBind($board->id, $file, true);
        }

        if (empty($args['_hashTags']) === false) {
            $this->tag->set($doc->id, $args['_hashTags'], $doc->instanceId);
        }

        $tags = Tag::getByTaggable($doc->id);
        foreach ($tags as $tag) {
            if (in_array($tag->word, $args['_hashTags']) === false) {
                $tags->delete();
            }
        }

        $board->getConnection()->commit();

        return $board->find($board->id);
    }

    /**
     * 문서 삭제
     *
     * @param Board $board
     * @param ConfigEntity $config
     * @return void
     * @throws \Exception
     */
    public function remove(Board $board, ConfigEntity $config)
    {
        $board->getConnection()->beginTransaction();

        // 덧글이 있다면 덧글들을 모두 삭제
        if ($config->get('recursiveDelete') === true) {
            $query = Board::where('head', $board->head);
            if ($board->reply !== '' && $board->reply !== null) {
                $query->where('reply', 'like', $board->reply . '%');
            }
            $items = $query->get();
            foreach ($items as $item) {
                $this->setModelConfig($item, $config);
                if ($item->slug !== null) {
                    $item->slug->delete();
                }
                $files = File::whereIn('id', $item->getFileIds())->get();
                foreach ($files as $file) {
                    $this->storage->unBind($item->id, $file, true);
                }
                // 태그 제거
                $item->delete();
            }
        } else {
            if ($board->slug !== null) {
                $board->slug->delete();
            }
            $files = File::whereIn('id', $board->getFileIds())->get();
            foreach ($files as $file) {
                $this->storage->unBind($board->id, $file, true);
            }
            // 태그 제거
            $board->delete();
        }

        $board->getConnection()->commit();
    }

    /**
     * 문서 휴지통 이동
     *
     * @param Board $board
     * @param ConfigEntity $config
     * @return void
     */
    public function trash(Board $board, ConfigEntity $config)
    {
        $board->getConnection()->beginTransaction();

        // 덧글이 있다면 덧글들을 모두 휴지통으로 옯긴다.
        if ($config->get('recursiveDelete') === true) {
            $query = Board::where('head', $board->head);
            if ($board->reply !== '' && $board->reply !== null) {
                $query->where('reply', 'like', $board->reply . '%');
            }
            $items = $query->get();
            foreach ($items as $item) {
                $this->setModelConfig($item, $config);
                $item->setTrash()->save();
            }
        } else {
            $board->setTrash()->save();
        }

        $board->getConnection()->commit();
    }

    /**
     * 문서 복원
     */
    public function restore(Board $board, ConfigEntity $config)
    {
        $board->getConnection()->beginTransaction();

        // 덧글이 있다면 덧글들을 모두 복원
        if ($config->get('recursiveDelete') === true) {
            $query = Board::where('head', $board->head);
            if ($board->reply !== '' && $board->reply !== null) {
                $query->where('reply', 'like', $board->reply . '%');
            }
            $items = $query->get();
            foreach ($items as $item) {
                $this->setModelConfig($item, $config);
                $item->setRestore()->save();
            }
        } else {
            $board->setRestore()->save();
        }

        $board->getConnection()->commit();
    }

    /**
     * 게시판 이동
     * Document Package 에서 comment 를 지원하지 않아서 사용할 수 있는 인터페이스가 없음
     *
     * @param string         $id             document id
     * @param ConfigEntity   $config         destination board config entity
     * @param CommentHandler $commentHandler comment handler
     */
    public function move(Board $board, ConfigEntity $config)
    {
        $board->getConnection()->beginTransaction();

        $dstInstanceId = $config->get('boardId');

        // 덧글이 있다면 덧글들을 모두 옯긴다.
        if ($config->get('recursiveDelete') === true) {
            $query = Board::where('head', $board->head);
            if ($board->reply !== '' && $board->reply !== null) {
                $query->where('reply', 'like', $board->reply . '%');
            }
            $items = $query->get();
            foreach ($items as $item) {
                $this->setModelConfig($item, $config);
                $item->instanceId = $dstInstanceId;
                $item->save();
            }
        } else {
            $board->instanceId = $dstInstanceId;
            $board->save();
        }

        $board->getConnection()->commit();
    }

    /**
     * 복사
     *
     * @param string       $id     document id
     * @param ConfigEntity $config destination board config entity
     * @param string       $newId  new document id
     * @return void
     */
    public function copy(Board $board, UserInterface $user, ConfigEntity $config)
    {
        $board->getConnection()->beginTransaction();

        $args = array_merge($board->getDynamicAttributes(), $board->getAttributes());
        $args['id'] = null;
        $args['instanceId'] = $config->get('boardId');
        $args['slug'] = $board->boardSlug->slug;
        $args['categoryItemId'] = $board->boardCategory->itemId;

        $this->add($args, $user, $config);

        $board->getConnection()->commit();
    }

    /**
     * Proxy, Division 관련 설정이 된 Document model 반환
     *
     * @param ConfigEntity $config board config entity
     * @return Board
     */
    public function getModel(ConfigEntity $config)
    {
        $instanceId = $config->get('boardId');
        $documentConfig = $this->documentHandler->getConfig($instanceId);
        $board = new Board;
        $board->setConfig($documentConfig, $this->documentHandler->getDivisionTableName($documentConfig));
        return $board;
    }

    /**
     * set model's config
     *
     * @param Board $board board model
     * @param ConfigEntity $config board config entity
     * @return Board
     */
    public function setModelConfig(Board $board, ConfigEntity $config)
    {
        $instanceId = $config->get('boardId');
        $documentConfig = $this->documentHandler->getConfig($instanceId);
        $board->setConfig($documentConfig, $this->documentHandler->getDivisionTableName($documentConfig));
        return $board;
    }

    /**
     * get notice
     *
     * @param ConfigEntity $config
     * @return mixed
     */
    public function getsNotice(ConfigEntity $config)
    {
        $query = $this->getModel($config)
            ->where('instanceId', $config->get('boardId'))
            ->where('status', Document::STATUS_NOTICE)
            ->where('display', Document::DISPLAY_VISIBLE)
            ->where('published', Document::PUBLISHED_PUBLISHED);

        return $query->get();
    }

    /**
     * 인터셥센을 이용해 서드파티가 처리할 수 있도록 메소드 사용
     *
     * @param Builder $query
     * @param Request $request
     * @param ConfigEntity $config
     * @return Builder
     */
    public function makeWhere(Builder $query, Request $request, ConfigEntity $config)
    {
        if ($request->get('title_content', '') !== '') {
            $query = $query->whereNested(function ($query) use ($request) {
                $query->where('title', 'like', sprintf('%%%s%%', $request->get('title_content')))
                    ->orWhere('content', 'like', sprintf('%%%s%%', $request->get('title_content')));
            });
        }

        if ($request->get('writer', '') !== '') {
            $query = $query->where('writer', $request->get('writer'));
        }

        if ($request->get('categoryItemId', '') !== '') {
            $query = $query->where('itemId', $request->get('categoryItemId'));
        }

        $query->getProxyManager()->wheres($query->getQuery(), $request->all());

        return $query;
    }

    /**
     * 인터셥센을 이용해 서드파티가 처리할 수 있도록 메소드 사용
     *
     * @param Builder $query
     * @param Request $request
     * @param ConfigEntity $config
     * @return Builder
     */
    public function makeOrder(Builder $query, Request $request, ConfigEntity $config)
    {
        if ($request->get('orderType') == null) {
            $query->orderBy('head', 'desc')->orderBy('reply', 'asc');
        } elseif ($request->get('orderType') == 'assentCount') {
            $query->orderBy('assentCount', 'desc')->orderBy('createdAt', 'desc');
        } elseif ($request->get('recentlyCreated') == 'assentCount') {
            $query->orderBy(Board::CREATED_AT, 'desc');
        } elseif ($request->get('recentlyUpdated') == 'assentCount') {
            $query->orderBy(Board::UPDATED_AT, 'desc');
        }

        $query->getProxyManager()->orders($query->getQuery(), $request->all());

        return $query;
    }

    public function getOrders()
    {
        return [
            ['value' => 'assentCount', 'text' => 'board::assentOrder'],
            ['value' => 'recentlyCreated', 'text' => 'board::recentlyCreated'],
            ['value' => 'recentlyUpdated', 'text' => 'board::recentlyUpdated'],
        ];
    }


    public function incrementReadCount(Board $board, $user)
    {
        if ($this->readCounter->has($board->id, $user) === false) {
            $this->readCounter->add($board->id, $user);
        }

        $board->readCount = $this->readCounter->getPoint($board->id);
        $board->save();
    }

    public function incrementVoteCount(Board $board, $user, $option)
    {
        if ($this->voteCounter->has($board->id, $user, $option) === false) {
            $this->voteCounter->add($board->id, $user, $option);
        }

        $columnName = 'assentCount';
        if ($option == 'dissent') {
            $columnName = 'dissentCount';
        }
        $board->{$columnName} = $this->voteCounter->getPoint($board->id, $option);
        $board->save();
    }

    public function decrementVoteCount(Board $board, $user, $option)
    {
        if ($this->voteCounter->has($board->id, $user, $option) === true) {
            $this->voteCounter->remove($board->id, $user, $option);
        }

        $columnName = 'assentCount';
        if ($option == 'dissent') {
            $columnName = 'dissentCount';
        }
        $board->{$columnName} = $this->voteCounter->getPoint($board->id, $option);
        $board->save();
    }

    public function hasVote(Board $board, $user, $option)
    {
        return $this->voteCounter->has($board->id, $user, $option);
    }

    /**
     * 수정 권한 확인
     *
     * @param $author 로그인 사용자 정보
     * @return bool
     */
    public function alterPerm($author)
    {
//        if ($this->isGuest($board) === true) {
//            return true;
//        }
//        if ($author instanceof Guest == true) {
//            return false;
//        }
//        if ($this->__get('userId') != $author->getId()) {
//            return false;
//        }
        return true;
    }
    /**
     * 삭제 권한 확인
     *
     * @param $author 로그인 사용자 정보
     * @return bool
     */
    public function deletePerm($author)
    {
//        if ($this->isGuest($board) === true) {
//            return true;
//        }
//        if ($author instanceof Guest == true) {
//            return false;
//        }
//        if ($this->__get('userId') != $author->getId()) {
//            return false;
//        }
        return true;
    }

}
