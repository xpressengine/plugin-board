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
use Xpressengine\Member\Entities\MemberEntityInterface;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Models\BoardCategory;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Storage\File;
use Xpressengine\Storage\Storage;


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
        Counter $readCounter,
        Counter $voteCounter
    ) {
        $this->documentHandler = $documentHandler;
        $this->storage = $storage;
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
     * @param array $args arguments
     * @return Board
     */
    public function add(array $args, MemberEntityInterface $user)
    {
        $model = new Board();
        $model->getConnection()->beginTransaction();

        $args['userId'] = $user->getId();
        if ($args['userId'] === null) {
            $args['userId'] = '';
        }
        if (empty($args['writer'])) {
            $args['writer'] = $user->getDisplayName();
        }

        // save Document
        $doc = $this->documentHandler->add($args);

        $board = Board::find($doc->id);

        // save Slug
        $slug = new BoardSlug([
            'slug' => $args['slug'],
            'title' => $args['title'],
            'instanceId' => $args['instanceId'],
        ]);
        $board->boardSlug()->save($slug);

        // save Category
        if (empty($args['itemId']) == false) {
            $boardCategory = new BoardCategory([
                'id' => $doc->id,
                'itemId' => $args['itemId'],
            ]);
            $boardCategory->save();
        }

        if (empty($args['_files']) === false) {
            foreach (File::whereIn('id', $args['_files'])->get() as $file) {
                $this->storage->bind($doc->id, $file);
            }
        }

        // 태그 등록
//        /** @var \Xpressengine\Tag\TagHandler $tag */
//        $tag = app('xe.tag');
//        $hashTags = array_unique($request->get('_hashTags', []));
//        $tag->set($this->boardId, $doc->id, $hashTags);

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

        // bind files
        // 업데이트 할 때 중복 bind 되어 fileable 이 계속 증가하는 오류가 있음
        $fileIds = [];
        if (empty($args['_files']) === false) {
            foreach (File::whereIn('id', $args['_files'])->get() as $file) {
                $fileIds[] = $file->id;
                $this->storage->bind($doc->id, $file);
            }
        }

        $files = File::whereIn('id', array_diff($board->getFileIds(), $fileIds))->get();
        foreach ($files as $file) {
            $this->storage->unBind($board->id, $file, true);
        }

        // 태그 등록
//        /** @var \Xpressengine\Tag\TagHandler $tag */
//        $tag = app('xe.tag');
//        $hashTags = array_unique(Input::get('hashTags', []));
//        $tag->set($this->boardId, $doc->id, $hashTags);

        $board->getConnection()->commit();

        return Board::find($board->id);
    }

    /**
     * Proxy, Division 관련 설정이 된 Document model 반환
     *
     * @param string $instanceId document instance id
     * @return Board
     */
    public function getModel($instanceId, ConfigEntity $config)
    {
        $documentConfig = $this->documentHandler->getConfigHandler()->get($config->get('boardId'));
        $board = new Board;
        $board->setDivision($documentConfig);
        $board->setProxyOptions($this->documentHandler->proxyOption($documentConfig));
        return $board;
    }

    public function getsNotice($instanceId, ConfigEntity $config)
    {
        $query = $this->getModel($instanceId, $config)
            ->where('instanceId', $instanceId)
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
     * @return QueryBuilder
     */
    public function makeWhere(Builder $query, Request $request)
    {
        return $query;
    }

    /**
     * 인터셥센을 이용해 서드파티가 처리할 수 있도록 메소드 사용
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function makeOrder(Builder $query, Request $request)
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

    public function hasVote(Board $board, $user, $option = '')
    {
        if ($option == '') {
            return $this->voteCounter->hasByName($board->id, $user);
        } else {
            return $this->voteCounter->has($board->id, $user, $option);
        }

    }


    /**
     * 비회원이 작성 글 여부 반환
     *
     * @return bool
     */
//    public function isGuest(Board $board)
//    {
//        return $board->userType == Board::USER_TYPE_GUEST;
//    }

    /**
     * 수정 권한 확인
     *
     * @param MemberEntityInterface $author 로그인 사용자 정보
     * @return bool
     */
    public function alterPerm(MemberEntityInterface $author)
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
     * @param MemberEntityInterface $author 로그인 사용자 정보
     * @return bool
     */
    public function deletePerm(MemberEntityInterface $author)
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
