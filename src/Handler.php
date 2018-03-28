<?php
/**
 * Handler
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
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
use Xpressengine\Plugins\Board\Models\BoardGalleryThumb;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Storage\File;
use Xpressengine\Storage\Storage;
use Xpressengine\Tag\Tag;
use Xpressengine\Tag\TagHandler;
use Xpressengine\User\Models\Guest;
use Xpressengine\User\UserInterface;
use Xpressengine\Storage\File as FileModel;
use Xpressengine\Plugins\Comment\Handler as CommentHandler;

/**
 * Handler
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
     * @var CommentHandler
     */
    protected $commentHandler;

    /**
     * Handler constructor.
     *
     * @param DocumentHandler $documentHandler document handler(Interception Proxy)
     * @param Storage         $storage         storage
     * @param TagHandler      $tag             tag
     * @param Counter         $readCounter     read counter
     * @param Counter         $voteCounter     vote counter
     * @param CommentHandler  $commentHandler  comment handler
     */
    public function __construct(
        DocumentHandler $documentHandler,
        Storage $storage,
        TagHandler $tag,
        Counter $readCounter,
        Counter $voteCounter,
        CommentHandler $commentHandler
    ) {
        $this->documentHandler = $documentHandler;
        $this->storage = $storage;
        $this->tag = $tag;
        $this->readCounter = $readCounter;
        $this->voteCounter = $voteCounter;
        $this->commentHandler = $commentHandler;
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
     * 글 등록
     *
     * @param array         $args   arguments
     * @param UserInterface $user   user
     * @param ConfigEntity  $config board config entity
     * @return Board
     */
    public function add(array $args, UserInterface $user, ConfigEntity $config)
    {
        $model = new Board;
        $model->getConnection()->beginTransaction();

        if (isset($args['type']) === false) {
            $args['type'] = BoardModule::getId();
        }
        $args['user_id'] = $user->getId();
        if ($args['user_id'] === null) {
            $args['user_id'] = '';
        }
        if (empty($args['writer'])) {
            $args['writer'] = $user->getDisplayName();
        }
        if (isset($args['certify_key']) === false) {
            $args['certify_key'] = '';
        }

        if ($config->get('anonymity') === true) {
            $args['writer'] = $config->get('anonymityName');
            $args['user_type'] = Board::USER_TYPE_ANONYMITY;
        }

        if ($user instanceof Guest) {
            $args['user_type'] = Board::USER_TYPE_GUEST;
        }

        // save Document
        $doc = $this->documentHandler->add($args);

        $board = Board::find($doc->id);

        if ($config->get('useApprove') === true) {
            $this->approveSetWait($board);
        }

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
        if (empty($args['allow_comment']) || $args['allow_comment'] !== '1') {
            $allowComment = 0;
        }
        $useAlarm = 1;
        if (empty($args['use_alarm']) || $args['use_alarm'] !== '1') {
            $useAlarm = 0;
        }
        $fileCount = FileModel::getByFileable($board->id)->count();

        $data = $board->boardData;
        if ($data === null) {
            $data = new BoardData([
                'allow_comment' => $allowComment,
                'use_alarm' => $useAlarm,
                'file_count' => $fileCount,
            ]);
        } else {
            $data->allow_comment = $allowComment;
            $data->use_alarm = $useAlarm;
            $data->file_count = $fileCount;
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
                'instance_id' => $args['instance_id'],
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
        if (empty($args['category_item_id']) == false) {
            // update 처리
            $boardCategory = $board->boardCategory;
            if ($boardCategory == null) {
                $boardCategory = new BoardCategory([
                    'target_id' => $board->id,
                    'item_id' => $args['category_item_id'],
                ]);
            } else {
                $boardCategory->item_id = $args['category_item_id'];
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
     * @return void
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
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            if (in_array($tag->word, $args['_hashTags']) === false) {
                $tag->delete();
            }
        }
    }

    /**
     * update document
     *
     * @param Board        $board  board model
     * @param array        $args   arguments
     * @param ConfigEntity $config board config entity
     * @return mixed
     */
    public function put(Board $board, array $args, ConfigEntity $config)
    {
        $board->getConnection()->beginTransaction();

        $attributes = $board->getAttributes();
        foreach ($args as $name => $value) {
            if (array_key_exists($name, $attributes)) {
                $board->{$name} = $value;
            }
        }

        $this->documentHandler->put($board);

        $this->saveSlug($board, $args);
        $this->saveCategory($board, $args);
        $this->setFiles($board, $args);
        $this->setTags($board, $args);
        $this->unsetTags($board, $args);
        $this->saveData($board, $args);
        $thumbnail = BoardGalleryThumb::find($board->id);
        if ($thumbnail !== null) {
            $thumbnail->delete();
        }

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
            /** @var Board[] $items */
            $items = $query->get();
            foreach ($items as $item) {
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
                $this->documentHandler->remove($item);
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
            $this->documentHandler->remove($board);
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
            /** @var Board[] $items */
            $items = $query->get();
            foreach ($items as $item) {
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
            /** @var Board[] $items */
            $items = $query->get();
            foreach ($items as $item) {
                $item->setRestore()->save();
            }
        } else {
            $board->setRestore()->save();
        }

        $board->getConnection()->commit();
    }

    /**
     * 게시판 이동
     *
     * @param Board        $board        board model
     * @param ConfigEntity $dstConfig    destination board config entity
     * @param ConfigEntity $originConfig original board config entity
     * @return void
     */
    public function move(Board $board, ConfigEntity $dstConfig, ConfigEntity $originConfig)
    {
        $board->getConnection()->beginTransaction();

        $dstInstanceId = $dstConfig->get('boardId');

        // 덧글이 있다면 덧글들을 모두 옯긴다.
        if ($originConfig->get('recursiveDelete') === true) {
            $query = Board::where('head', $board->head)->where('id', '<>', $board->id);
            if ($board->reply !== '' && $board->reply !== null) {
                $query->where('reply', 'like', $board->reply . '%');
            }
            /** @var Board[] $items */
            $items = $query->get();
            foreach ($items as $item) {
                $this->move($item, $dstConfig, $originConfig);
            }
        }

        $board->instance_id = $dstInstanceId;
        $board->save();

        $slug = $board->boardSlug;
        $slug->instance_id = $dstInstanceId;
        $slug->save();

        // 댓글 인스턴스 변경 처리
        $this->commentHandler->moveByTarget($board);

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
        $args['instance_id'] = $config->get('boardId');
        $args['slug'] = $board->boardSlug->slug;
        $args['category_item_id'] = '';
        $boardCategory = $board->boardCategory;
        if ($boardCategory != null) {
            $args['category_item_id'] = $boardCategory->item_id;
        }

        $this->add($args, $user, $config);

        $board->getConnection()->commit();
    }

    /**
     * set approve status
     *
     * @param Board $board board model
     * @return void
     */
    public function approveSetApprove(Board $board)
    {
        $board->setApprove();
        $board->save();
    }

    /**
     * set reject status
     *
     * @param Board $board board model
     * @return void
     */
    public function approveSetReject(Board $board)
    {
        $board->setReject();
        $board->save();
    }

    /**
     * set wait status
     *
     * @param Board $board board model
     * @return void
     */
    public function approveSetWait(Board $board)
    {
        $board->setApproveWait();
        $board->save();
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
        if ($request->get('title_pure_content') != null && $request->get('title_pure_content') !== '') {
            $query = $query->whereNested(function ($query) use ($request) {
                $query->where('title', 'like', sprintf('%%%s%%', $request->get('title_pure_content')))
                    ->orWhere('pure_content', 'like', sprintf('%%%s%%', $request->get('title_pure_content')));
            });
        }

        if ($request->get('title_content') != null && $request->get('title_content') !== '') {
            $query = $query->whereNested(function ($query) use ($request) {
                $query->where('title', 'like', sprintf('%%%s%%', $request->get('title_content')))
                    ->orWhere('content', 'like', sprintf('%%%s%%', $request->get('title_content')));
            });
        }

        if ($request->get('writer') != null && $request->get('writer') !== '') {
            $query = $query->where('writer', $request->get('writer'));
        }

        if ($request->get('category_item_id') != null && $request->get('category_item_id') !== '') {
            $query = $query->where('item_id', $request->get('category_item_id'));
        }

        if ($request->get('start_created_at') != null && $request->get('start_created_at') !== '') {
            $query = $query->where('created_at', '>=', $request->get('start_created_at') . ' 00:00:00');
        }

        if ($request->get('end_created_at') != null && $request->get('end_created_at') !== '') {
            $query = $query->where('created_at', '<=', $request->get('end_created_at') . ' 23:59:59');
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
        $orderType = $request->get('order_type', '');
        if ($orderType === '' && $config->get('orderType') != null) {
            $orderType = $config->get('orderType', '');
        }

        if ($orderType == '') {
            $query->orderBy('head', 'desc');
        } elseif ($orderType == 'assent_count') {
            $query->orderBy('assent_count', 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recently_created') {
            $query->orderBy(Board::CREATED_AT, 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recently_updated') {
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
            ['value' => 'assent_count', 'text' => 'board::assentOrder'],
            ['value' => 'recently_created', 'text' => 'board::recentlyCreated'],
            ['value' => 'recently_updated', 'text' => 'board::recentlyUpdated'],
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

        $board->read_count = $this->readCounter->getPoint($board->id);
        $board->timestamps = false;
        $board->save();
    }

    /**
     * vote
     *
     * @param Board         $board  board model
     * @param UserInterface $user   user
     * @param string        $option 'assent' or 'dissent'
     * @param int           $point  vote point
     * @return void
     */
    public function vote(Board $board, UserInterface $user, $option, $point = 1)
    {
        if ($this->voteCounter->has($board->id, $user, $option) === false) {
            $this->incrementVoteCount($board, $user, $option, $point);
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
     * @param int           $point  vote point
     * @return void
     */
    public function incrementVoteCount(Board $board, UserInterface $user, $option, $point = 1)
    {
        $this->voteCounter->add($board->id, $user, $option, $point);

        $columnName = 'assent_count';
        if ($option == 'dissent') {
            $columnName = 'dissent_count';
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

        $columnName = 'assent_count';
        if ($option == 'dissent') {
            $columnName = 'dissent_count';
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

    /**
     * check has favorite
     *
     * @param string $boardId board id
     * @param string $userId  user id
     * @return bool
     */
    public function hasFavorite($boardId, $userId)
    {
        return BoardFavorite::where('target_id', $boardId)->where('user_id', $userId)->exists();
    }

    /**
     * add favorite
     *
     * @param string $boardId board id
     * @param string $userId  user id
     * @return BoardFavorite
     */
    public function addFavorite($boardId, $userId)
    {
        if ($this->hasFavorite($boardId, $userId) === true) {
            throw new AlreadyExistFavoriteHttpException;
        }

        $favorite = new BoardFavorite;
        $favorite->target_id = $boardId;
        $favorite->user_id = $userId;
        $favorite->save();

        return $favorite;
    }

    /**
     * remove favorite
     *
     * @param string $boardId board id
     * @param string $userId  user id
     * @return void
     */
    public function removeFavorite($boardId, $userId)
    {
        if ($this->hasFavorite($boardId, $userId) === false) {
            throw new NotFoundFavoriteHttpException;
        }

        BoardFavorite::where('target_id', $boardId)->where('user_id', $userId)->delete();
    }

    /**
     * $request, $id 로 현재의 글이 리스트에서 몇 페이지에 표시되야 하는지 추측
     *
     * order by A desc 인 경우 (order 가 1개일 경우)
     * ```
     * and (A >= 'value')
     * ```
     *
     * order by A desc, B desc 인 경우 (order 가 2일 이상이면 같은 방식)
     * ```
     * and (
     *   (A >= 'value')
     *   or (A = 'value' and B >= 'value')
     * )
     * ```
     *
     * order by A desc, B desc, C desc 인 경우 (order 가 3개인 경우)
     * ```
     * and (
     *   (A >= 'value')
     *   or (A = 'value' and B >= 'value')
     *   or (A = 'value' and B = 'value' and C >= 'value')
     * )
     * ```
     *
     * order by A desc, B desc, C asc, D desc 인 경우 (order 가 4개인 경우)
     * ```
     * and (
     *   (A >= 'value')
     *   or (A = 'value' and B >= 'value')
     *   or (A = 'value' and B = 'value' and C <= 'value')
     *   or (A = 'value' and B = 'value' and C = 'value', D >= 'value')
     * )
     * ```
     *
     * @param Builder      $query  orm builder
     * @param ConfigEntity $config board config
     * @param string       $id     document id
     * @return int
     */
    public function pageResolver(Builder $query, ConfigEntity $config, $id)
    {
        $clone = clone $query;
        /** @var Board $model */
        $model = Board::division($config->get('boardId'));
        $doc = $model->find($id);

        $orders = $clone->getQuery()->orders;
        $clone->where(function ($clone) use ($orders, $doc) {
            $orderCount = count($orders);

            for ($i=0; $i<$orderCount; $i++) {
                $clone->Orwhere(function ($clone) use ($orders, $doc, $i) {
                    if ($i != 0) {
                        for ($j=0; $j<$i; $j++) {
                            $op = '=';
                            $clone->where($orders[$j]['column'], $op, $doc->{$orders[$j]['column']});
                        }
                    }

                    $op = '>=';
                    if ($orders[$i]['direction'] == 'asc') {
                        $op = '<=';
                    }
                    $clone->where($orders[$i]['column'], $op, $doc->{$orders[$i]['column']});
                });
            }
        });

        $count = $clone->count();

        $page = (int)($count / $config->get('perPage'));
        if ($count % $config->get('perPage') != 0) {
            ++$page;
        }
        if ($page == 0) {
            $page = 1;
        }

        return $page;
    }
}
