<?php
/**
 * Board handler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\Counter\Counter;
use Xpressengine\Database\Eloquent\Builder;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Board\Exceptions\AlreadyExistFavoriteHttpException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundFavoriteHttpException;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Models\BoardCategory;
use Xpressengine\Plugins\Board\Models\BoardData;
use Xpressengine\Plugins\Board\Models\BoardFavorite;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Plugins\Board\Modules\Board as BoardModule;
use Xpressengine\Storage\File;
use Xpressengine\Storage\Storage;
use Xpressengine\Tag\Tag;
use Xpressengine\Tag\TagHandler;
use Xpressengine\User\Models\Guest;
use Xpressengine\User\UserInterface;
use Xpressengine\Storage\File as FileModel;

/**
 * Board handler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
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

    /**
     * @var Counter
     */
    protected $readCounter;

    /**
     * @var Counter
     */
    protected $voteCounter;


    /**
     * Handler constructor.
     *
     * @param DocumentHandler $documentHandler document handler(Interception Proxy)
     * @param Storage         $storage         storage
     * @param TagHandler      $tag             tag
     * @param Counter         $readCounter     read counter
     * @param Counter         $voteCounter     vote counter
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

    /**
     * get read counter
     *
     * @return Counter
     */
    public function getReadCounter()
    {
        return $this->readCounter;
    }

    /**
     * get vote counter
     *
     * @return Counter
     */
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
     * @return Board
     */
    public function add(array $args, UserInterface $user, ConfigEntity $config)
    {
        $model = $this->getModel($config);
        $model->getConnection()->beginTransaction();

        $args['type'] = BoardModule::getId();
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

        if ($config->get('anonymity') === true) {
            $args['writer'] = $config->get('anonymityName');
            $args['userType'] = Board::USER_TYPE_ANONYMITY;
        }

        // save Document
        $doc = $this->documentHandler->add($args);
        $model = $this->getModel($config);
        $board = $model->find($doc->id);
        $this->setModelConfig($board, $config);

        $this->saveSlug($board, $args);
        $this->saveCategory($board, $args);
        $this->setFiles($board, $args);
        $this->setTags($board, $args);
        $this->saveData($board, $args);

        $model->getConnection()->commit();

        return $board;
    }

    /**
     * save data
     *
     * @param Board $board board model
     * @param array $args  arguments
     * @return void
     */
    protected function saveData(Board $board, array $args)
    {
        $allowComment = 1;
        if (empty($args['allowComment']) || $args['allowComment'] !== '1') {
            $allowComment = 0;
        }
        $useAlarm = 1;
        if (empty($args['useAlarm']) || $args['useAlarm'] !== '1') {
            $useAlarm = 0;
        }
        $fileCount = FileModel::getByFileable($board->id)->count();

        $data = $board->boardData;
        if ($data === null) {
            $data = new BoardData([
                'allowComment' => $allowComment,
                'useAlarm' => $useAlarm,
                'fileCount' => $fileCount,
            ]);
        } else {
            $data->allowComment = $allowComment;
            $data->useAlarm = $useAlarm;
            $data->fileCount = $fileCount;
        }

        $board->boardData()->save($data);
    }

    /**
     * save slug
     *
     * @param Board $board board model
     * @param array $args  arguments
     * @return void
     */
    protected function saveSlug(Board $board, array $args)
    {
        $slug = $board->boardSlug;
        if ($slug === null) {
            $args['slug'] = BoardSlug::make($args['slug'], $board->id);
            $slug = new BoardSlug([
                'slug' => $args['slug'],
                'title' => $args['title'],
                'instanceId' => $args['instanceId'],
            ]);
        } else {
            $slug->slug = $args['slug'];
            $slug->title = $board->title;
        }

        $board->boardSlug()->save($slug);
    }

    /**
     * save category
     *
     * @param Board $board board model
     * @param array $args  arguments
     * @return void
     */
    protected function saveCategory(Board $board, array $args)
    {
        // save Category
        if (empty($args['categoryItemId']) == false) {
            // update 처리
            $boardCategory = $board->boardCategory;
            if ($boardCategory == null) {
                $boardCategory = new BoardCategory([
                    'targetId' => $board->id,
                    'itemId' => $args['categoryItemId'],
                ]);
            } else {
                $boardCategory->itemId = $args['categoryItemId'];
            }

            $boardCategory->save();
        }
    }

    /**
     * set files
     *
     * @param Board $board board model
     * @param array $args  arguments
     * @return array
     * @todo 업데이트 할 때 중복 bind 되어 fileable 이 계속 증가하는 오류가 있음
     */
    protected function setFiles(Board $board, array $args)
    {
        $fileIds = [];
        if (empty($args['_files']) === false) {
            $this->storage->sync($board->getKey(), $args['_files']);
        }
        return $fileIds;
    }

    /**
     * unset files
     *
     * @param Board $board   board model
     * @param array $fileIds current uploaded file ids
     * @retunr void
     */
    protected function unsetFiles(Board $board, array $fileIds)
    {
        $files = File::whereIn('id', array_diff($board->getFileIds(), $fileIds))->get();
        foreach ($files as $file) {
            $this->storage->unBind($board->id, $file, true);
        }
    }

    /**
     * set tags
     *
     * @param Board $board board model
     * @param array $args  arguments
     * @return void
     */
    protected function setTags(Board $board, array $args)
    {
        if (empty($args['_hashTags']) === false) {
            $this->tag->set($board->getKey(), $args['_hashTags']);
        }
    }

    /**
     * unset tags
     *
     * @param Board $board board model
     * @param array $args  arguments
     * @return void
     */
    protected function unsetTags(Board $board, array $args)
    {
        $tags = Tag::getByTaggable($board->id);
        foreach ($tags as $tag) {
            if (in_array($tag->word, $args['_hashTags']) === false) {
                $tags->delete();
            }
        }
    }

    /**
     * update document
     *
     * @param Board $board board model
     * @param array $args  arguments
     * @return mixed
     */
    public function put(Board $board, array $args)
    {
        $board->getConnection()->beginTransaction();

        foreach ($args as $name => $value) {
            if ($board->{$name} !== null) {
                $board->{$name} = $value;
            }
        }

        $doc = $this->documentHandler->put($board);

        $this->saveSlug($board, $args);
        $this->saveCategory($board, $args);
        $fileIds = $this->setFiles($board, $args);
        $this->setTags($board, $args);
        $this->unsetTags($board, $args);
        $this->saveData($board, $args);

        $board->getConnection()->commit();

        return $board->find($board->id);
    }

    /**
     * 문서 삭제
     *
     * @param Board        $board  board model
     * @param ConfigEntity $config board config entity
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
                if ($item->boardSlug !== null) {
                    $item->boardSlug->delete();
                }
                if ($item->boardCategory !== null) {
                    $item->boardCategory->delete();
                }
                $files = File::whereIn('id', $item->getFileIds())->get();
                foreach ($files as $file) {
                    $this->storage->unBind($item->id, $file, true);
                }
                $tags = Tag::getByTaggable($item->id);
                foreach ($tags as $tag) {
                    $tag->delete();
                }
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
            $tags = Tag::getByTaggable($board->id);
            foreach ($tags as $tag) {
                $tag->delete();
            }
            $board->delete();
        }

        $board->getConnection()->commit();
    }

    /**
     * 문서 휴지통 이동
     *
     * @param Board        $board  board model
     * @param ConfigEntity $config board config entity
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
     * 휴지통에서 문서 복원
     *
     * @param Board        $board  board model
     * @param ConfigEntity $config board config entity
     * @return void
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
     * @param Board        $board  board model
     * @param ConfigEntity $config board config entity
     * @return void
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

                $slug = $board->boardSlug;
                $slug->instanceId = $dstInstanceId;
                $slug->save();
            }
        } else {
            $board->instanceId = $dstInstanceId;
            $board->save();

            $slug = $board->boardSlug;
            $slug->instanceId = $dstInstanceId;
            $slug->save();
        }

        $board->getConnection()->commit();
    }

    /**
     * copy
     *
     * @param Board         $board  board model
     * @param UserInterface $user   user
     * @param ConfigEntity  $config board config entity
     * @return void
     */
    public function copy(Board $board, UserInterface $user, ConfigEntity $config)
    {
        $board->getConnection()->beginTransaction();

        $args = array_merge($board->getDynamicAttributes(), $board->getAttributes());
        $args['id'] = null;
        $args['instanceId'] = $config->get('boardId');
        $args['slug'] = $board->boardSlug->slug;
        $args['categoryItemId'] = '';
        $boardCategory = $board->boardCategory;
        if ($boardCategory != null) {
            $args['categoryItemId'] = $boardCategory->itemId;
        }

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
     * @param Board        $board  board model
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
    public function getsNotice(ConfigEntity $config, $userId)
    {
        $query = $this->getModel($config)
            ->where('instanceId', $config->get('boardId'))
            ->where('status', Document::STATUS_NOTICE)
            ->whereIn('display', [Document::DISPLAY_VISIBLE, Document::DISPLAY_SECRET])
            ->where('published', Document::PUBLISHED_PUBLISHED)
            ->orderBy('head', 'desc');

        // eager loading
        $query->with(['favorite' => function($favoriteQuery) use ($userId) {
            $favoriteQuery->where('userId', $userId);
        }, 'slug', 'data']);

        return $query->get();
    }

    /**
     * 인터셥센을 이용해 서드파티가 처리할 수 있도록 메소드 사용
     *
     * @param Builder      $query   board model query builder
     * @param Request      $request request
     * @param ConfigEntity $config  board config entity
     * @return Builder
     */
    public function makeWhere(Builder $query, Request $request, ConfigEntity $config)
    {
        if ($request->get('title_pureContent', '') !== '') {
            $query = $query->whereNested(function ($query) use ($request) {
                $query->where('title', 'like', sprintf('%%%s%%', $request->get('title_pureContent')))
                    ->orWhere('pureContent', 'like', sprintf('%%%s%%', $request->get('title_pureContent')));
            });
        }

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

        if ($request->get('startCreatedAt', '') !== '') {
            $query = $query->where('createdAt', '>=', $request->get('startCreatedAt') . ' 00:00:00');
        }

        if ($request->get('endCreatedAt', '') !== '') {
            $query = $query->where('createdAt', '<=', $request->get('endCreatedAt') . ' 23:59:59');
        }

        $query->getProxyManager()->wheres($query->getQuery(), $request->all());

        return $query;
    }

    /**
     * 인터셥센을 이용해 서드파티가 처리할 수 있도록 메소드 사용
     *
     * @param Builder      $query   board model query builder
     * @param Request      $request request
     * @param ConfigEntity $config  board config entity
     * @return Builder
     */
    public function makeOrder(Builder $query, Request $request, ConfigEntity $config)
    {
        $orderType = $request->get('orderType', '');
        if ($orderType === '' && $config->get('orderType') != null) {
            $orderType = $config->get('orderType', '');
        }

        if ($orderType == '') {
            $query->orderBy('head', 'desc');
        } elseif ($orderType == 'assentCount') {
            $query->orderBy('assentCount', 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recentlyCreated') {
            $query->orderBy(Board::CREATED_AT, 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recentlyUpdated') {
            $query->orderBy(Board::UPDATED_AT, 'desc')->orderBy('head', 'desc');
        }

        $query->getProxyManager()->orders($query->getQuery(), $request->all());

        return $query;
    }

    /**
     * get orders
     *
     * @return array
     */
    public function getOrders()
    {
        return [
            ['value' => 'assentCount', 'text' => 'board::assentOrder'],
            ['value' => 'recentlyCreated', 'text' => 'board::recentlyCreated'],
            ['value' => 'recentlyUpdated', 'text' => 'board::recentlyUpdated'],
        ];
    }

    /**
     * increment read count
     *
     * @param Board         $board board model
     * @param UserInterface $user  user
     * @return void
     */
    public function incrementReadCount(Board $board, UserInterface $user)
    {
        if ($this->readCounter->has($board->id, $user) === false) {
            $this->readCounter->add($board->id, $user);
        }

        $board->readCount = $this->readCounter->getPoint($board->id);
        $board->save();
    }

    /**
     * vote
     *
     * @param Board         $board  board model
     * @param UserInterface $user   user
     * @param string        $option 'assent' or 'dissent'
     * @return void
     */
    public function vote(Board $board, UserInterface $user, $option)
    {
        if ($this->voteCounter->has($board->id, $user, $option) === false) {
            $this->incrementVoteCount($board, $user, $option);
        } else {
            $this->decrementVoteCount($board, $user, $option);
        }
    }

    /**
     * increment vote count
     *
     * @param Board         $board  board model
     * @param UserInterface $user   user
     * @param string        $option 'assent' or 'dissent'
     * @return void
     */
    public function incrementVoteCount(Board $board, UserInterface $user, $option)
    {
        $this->voteCounter->add($board->id, $user, $option);

        $columnName = 'assentCount';
        if ($option == 'dissent') {
            $columnName = 'dissentCount';
        }
        $board->{$columnName} = $this->voteCounter->getPoint($board->id, $option);
        $board->save();
    }

    /**
     * decrement vote count
     *
     * @param Board         $board  board model
     * @param UserInterface $user   user
     * @param string        $option 'assent' or 'dissent'
     * @return void
     */
    public function decrementVoteCount(Board $board, UserInterface $user, $option)
    {
        $this->voteCounter->remove($board->id, $user, $option);

        $columnName = 'assentCount';
        if ($option == 'dissent') {
            $columnName = 'dissentCount';
        }
        $board->{$columnName} = $this->voteCounter->getPoint($board->id, $option);
        $board->save();
    }

    /**
     * has vote
     *
     * @param Board         $board  board model
     * @param UserInterface $user   user
     * @param string        $option 'assent' or 'dissent'
     * @return bool
     */
    public function hasVote(Board $board, $user, $option)
    {
        return $this->voteCounter->has($board->id, $user, $option);
    }

    public function hasFavorite($boardId, $userId)
    {
        return BoardFavorite::where('targetId', $boardId)->where('userId', $userId)->exists();
    }

    public function addFavorite($boardId, $userId)
    {
        if ($this->hasFavorite($boardId, $userId) === true) {
            throw new AlreadyExistFavoriteHttpException;
        }

        $favorite = new BoardFavorite;
        $favorite->targetId = $boardId;
        $favorite->userId = $userId;
        $favorite->save();
    }

    public function removeFavorite($boardId, $userId)
    {
        if ($this->hasFavorite($boardId, $userId) === false) {
            throw new NotFoundFavoriteHttpException;
        }

        BoardFavorite::where('targetId', $boardId)->where('userId', $userId)->delete();
    }
}
