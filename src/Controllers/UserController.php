<?php
/**
 * UserController
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
namespace Xpressengine\Plugins\Board\Controllers;

use Input;
use Redirect;
use Xpressengine\Keygen\Keygen;
use Exception;
use Presenter;
use XeDB;
use Auth;
use Frontend;
use Xpressengine\Document\DocumentEntity;
use Xpressengine\Document\Exceptions\DocumentNotExistsException;
use Validator;
use Xpressengine\Member\Entities\Guest;
use URL;
use App\Http\Controllers\Controller;
use Xpressengine\Member\Entities\MemberEntityInterface;
use Xpressengine\Plugins\Board\Exceptions\DeleteFailException;
use Xpressengine\Plugins\Board\Exceptions\InvalidIdentifyException;
use Xpressengine\Plugins\Board\Exceptions\InvalidRequestException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundDocumentException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundInstanceIdException;
use Xpressengine\Plugins\Board\Exceptions\NotMatchedCertifyKeyException;
use Xpressengine\Plugins\Board\Exceptions\RequiredValueException;
use Xpressengine\Plugins\Board\Exceptions\ValidationException;
use Xpressengine\Plugins\Board\ItemEntity;
use Xpressengine\Plugins\Board\Module\Board;
use Xpressengine\Plugins\Board\PermissionHandler;
use Xpressengine\Routing\InstanceConfig;
use Illuminate\Http\Request;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Permission\Action;
use Xpressengine\Plugins\Board\Exceptions\AccessDeniedHttpException;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Validator as BoardValidator;

/**
 * UserController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class UserController extends Controller
{
    /**
     * @var string
     */
    public $currentUrl;

    /**
     * @var string
     */
    public $boardId;

    /**
     * @var ConfigEntity
     */
    public $config;

    /**
     * @var Handler
     */
    public $handler;

    /**
     * @var PermissionHandler
     */
    public $permissionHandler;

    /**
     * @var ConfigHandler
     */
    public $configHandler;

    /**
     * @var UrlHandler
     */
    public $urlHandler;

    /**
     * @var bool
     */
    public $isManager = false;

    /**
     * create instance
     */
    public function __construct()
    {
        $instanceConfig = InstanceConfig::instance();
        $this->currentUrl = $instanceConfig->getUrl();
        $this->boardId = $instanceConfig->getInstanceId();
        if ($this->boardId === null) {
            throw new NotFoundInstanceIdException;
        }

        $this->handler = app('xe.board.handler');
        $this->urlHandler = app('xe.board.url');
        $this->configHandler = app('xe.board.config');

        // set config
        $this->config = $this->configHandler->get($this->boardId);
        $this->handler->setConfig($this->config);
        $this->urlHandler->setConfig($this->config);

        // check is manager
        $this->permissionHandler = app('xe.board.permission');
        if (
            Auth::guest() === false &&
            $this->permissionHandler->get($this->boardId)->ables(PermissionHandler::ACTION_MANAGE) === true
        ) {
            $this->isManager = true;
        }

        // set skin
        /** @var \Xpressengine\Presenter\Presenter $presenter */
        $presenter = app('xe.presenter');
        $presenter->setSkin(Board::getId());
        //$presenter->htmlRenderPopup();

        $presenter->share('config', $this->config);
        $presenter->share('currentUrl', $this->currentUrl);
        $presenter->share('boardId', $this->boardId);
        $presenter->share('handler', $this->handler);
        $presenter->share('permissionHandler', $this->permissionHandler);
        $presenter->share('configHandler', $this->configHandler);
        $presenter->share('urlHandler', $this->urlHandler);
        $presenter->share('isManager', $this->isManager);
    }

    /**
     * index
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function index()
    {
        if ($this->permissionHandler->hasList($this->boardId) === false) {
            throw new AccessDeniedHttpException;
        }

        /** @var DataImporter $dataImporter */
        $dataImporter = app('xe.board.dataImporter')->init($this);

        return Presenter::makeAll('index', $dataImporter->index());
    }

    /**
     * validate
     *
     * @param Request $request          request
     * @param array   $rules            rules
     * @param array   $messages         messages
     * @param array   $customAttributes custom attributes
     * @return void
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Input::flash();
            $e = new ValidationException;
            $e->setMessage($validator->errors()->first());
            throw $e;
        }
    }

    /**
     * 글 등록 페이지
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function create()
    {
        if ($this->permissionHandler->hasCreate($this->boardId) === false) {
            throw new AccessDeniedHttpException;
        }

        /** @var MemberEntityInterface $user */
        $user = Auth::user();

        $item = new ItemEntity();
        // isset parent id
        $parent = null;
        if (Input::get('parentId') != null) {
            $parent = $this->handler->get(Input::get('parentId'), $this->boardId);
            $item->parentId = $parent->id;
        }

        // get rules
        /** @var BoardValidator $validator */
        $validator = app('xe.board.validator');
        $rules = $validator->getCreateRule($user, $this->config);
        // set frontend rule
        Frontend::rule('board', $rules);

        return Presenter::makeAll('create', [
            'action' => 'create',
            'handler' => $this->handler,
            'item' => $item,
            'parent' => $parent,
            'user' => $user,
            'formColumns' => $this->configHandler->formColumns($this->boardId),
        ]);
    }

    /**
     * 글 등록 post
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        if ($this->permissionHandler->hasCreate($this->boardId) === false) {
            throw new AccessDeniedHttpException;
        }

        /** @var \Illuminate\Http\Request $request */
        $request = app('request');
        $user = Auth::user();

        // get rules
        /** @var BoardValidator $validator */
        $validator = app('xe.board.validator');
        $rules = $validator->getCreateRule($user, $this->config);
        $this->validate($request, $rules);

        // make document entity
        $doc = new DocumentEntity($this->handler->documentFilter($request->all()));
        $doc->id = (new Keygen())->generate();
        $doc->instanceId = $this->boardId;

        // 공지
        $doc->notice(false);
        if ($request->get('status') == 'notice' && $this->isManager) {
            $doc->notice(true);
        }

        // make board item entity
        $item = $this->handler->makeItem($doc, $request, $user);

        // 암호 설정
        /** @var \Xpressengine\Plugins\Board\IdentifyManager $identifyManager */
        $identifyManager = app('xe.board.identify');
        if ($doc->certifyKey != null) {
            $doc->certifyKey = $identifyManager->hash($doc->certifyKey);
        }
        $item->setDocument($doc);

        XeDB::beginTransaction();

        // document insert
        $this->handler->add($item, $this->config);

        // 태그 등록
        /** @var \Xpressengine\Tag\TagHandler $tag */
        $tag = app('xe.tag');
        $hashTags = array_unique($request->get('_hashTags', []));
        $tag->set($this->boardId, $doc->id, $hashTags);

        XeDB::commit();

        // 답글인 경우 부모글이 있는 곳으로 이동한다.(최대한..)
        if (Input::get('parentId') != '') {
            return Redirect::to(
                $this->urlHandler->get('index', $this->urlHandler->queryStringToArray(Input::get('queryString')))
            );
        } else {
            return Redirect::to($this->urlHandler->get('index'));
        }
    }

    /**
     * 비회원 인증 페이지
     * @param DocumentEntity $doc     document entity
     * @param null|string    $referer referer url (return page url)
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function identify(DocumentEntity $doc, $referer = null)
    {
        // 레퍼러는 현재 url
        if ($referer == null) {
            $referer = URL::current();
        }
        return Presenter::make('identify', [
            'doc' => $doc,
            'referer' => $referer,
        ]);
    }

    /**
     * 인증 처리
     * return to referer date url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function certify()
    {
        $item = $this->handler->get(Input::get('id'), $this->boardId);

        if ($item->certifyKey == '') {
            throw new InvalidRequestException;
        }

        /** @var \Xpressengine\Plugins\Board\IdentifyManager $identifyManager */
        $identifyManager = app('xe.board.identify');
        if ($identifyManager->verify($item, Input::get('email'), Input::get('certifyKey')) === false) {
            throw new NotMatchedCertifyKeyException;
        }

        // 인증 되었다면 DB의 인증키를 세션에 저장
        $identifyManager->create($item);

        return Redirect::to(Input::get('referer', 'edit'));
    }

    /**
     * edit
     *
     * @param string $url url
     * @param string $id  document id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function edit($url, $id)
    {
        /** @var \Illuminate\Http\Request $request */
        $request = app('request');
        $user = Auth::user();

        $config = $this->configHandler->get($this->boardId);

        $item = new ItemEntity();
        if ($id !== null) {
            $item = $this->handler->get($id, $this->boardId);
        }

        $doc = $item->getDocument();

        if ($doc === null) {
            throw new DocumentNotExistsException;
        }

        // 비회원이 작성 한 글일 때 인증페이지로 이동
        /** @var \Xpressengine\Plugins\Board\IdentifyManager $identifyManager */
        $identifyManager = app('xe.board.identify');
        if (
            $doc->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            $user->getRating() != 'super'
        ) {
            return $this->identify($doc);
        }

        // 접근 권한 확인
        if ($this->permissionHandler->hasCreate($this->boardId) === false) {
            throw new AccessDeniedHttpException;
        }

        /** @var \Xpressengine\Plugins\Board\Validator $validator */
        $validator = app('xe.board.validator');
        $rules = $validator->makeRule($this->config);
        if ($user instanceof Guest) {
            $rules = array_merge($rules, $validator->guestUpdate());
        }

        Frontend::rule('board', $rules);

        $parent = null;

        $formColumns = $this->configHandler->formColumns($this->boardId);

        return Presenter::make('edit', [
            'config' => $config,
            'handler' => $this->handler,
            'item' => $item,
            'parent' => $parent,
            'formColumns' => $formColumns,
        ]);
    }

    /**
     * update
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
    {
        /** @var \Illuminate\Http\Request $request */
        $request = app('request');
        $user = Auth::user();
        $id = $request->get('id');

        if ($id === null) {
            throw new RequiredValueException;
        }

        // 글 수정 시 게시판 설정이 아닌 글의 상태에 따른 처리가 되어야 한다.
        $item = $this->handler->get($id, $this->boardId);
        $doc = $item->getDocument();

        // 비회원이 작성 한 글 인증
        // 비회원이 작성 한 글일 때 인증페이지로 이동
        /** @var \Xpressengine\Plugins\Board\IdentifyManager $identifyManager */
        $identifyManager = app('xe.board.identify');
        if (
            $doc->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            $user->getRating() != 'super'
        ) {
            $e = new InvalidIdentifyException;
            throw $e;
        }

        /** @var \Xpressengine\Plugins\Board\Validator $validator */
        $validator = app('xe.board.validator');
        $rules = $validator->makeRule($this->config);
        if ($user instanceof Guest) {
            $rules = array_merge($rules, $validator->guestUpdate());
        }
        $this->validate($request, $rules);

        foreach ($this->handler->documentFilter($request->all()) as $name => $value) {
            $doc->{$name} = $value;
        }

        // 공지
        $doc->notice(false);
        if ($request->get('status') == 'notice' && $this->isManager) {
            $doc->notice(true);
        }

        $item->setDocument($doc);

        /** @var \Xpressengine\Storage\Storage $storage */
        if (($fileIds = $request->get('_files')) !== null) {
            $storage = app('xe.storage');
            $item->setFiles($storage->getsIn($fileIds));
        }

        // 암호 설정
        if ($doc->certifyKey != '') {
            $doc->certifyKey = $identifyManager->hash($doc->certifyKey);
        }

        // 비회원 글 수정시 비밀번호를 입력 안한 경우 원래 비밀번호로 설
        $origin = $doc->getOriginal();
        if ($origin['certifyKey'] != '' && $doc->certifyKey == '') {
            $doc->certifyKey = $origin['certifyKey'];
        }
        $item->setDocument($doc);

        XeDB::beginTransaction();

        $this->handler->put($item);

        $doc = $item->getDocument();


        // 비회원 비밀번호를 변경 한 경우 세션 변경
        if ($origin['certifyKey'] != '' && $origin['certifyKey'] != $doc->certifyKey) {
            $identifyManager->destroy($item);
            $identifyManager->create($item);
        }

        // 태그 등록
        /** @var \Xpressengine\Tag\TagHandler $tag */
        $tag = app('xe.tag');
        $hashTags = array_unique(Input::get('hashTags', []));
        $tag->set($this->boardId, $doc->id, $hashTags);

        XeDB::commit();

        return Redirect::to(
            $this->urlHandler->getShow($item, $this->urlHandler->queryStringToArray(Input::get('queryString')))
        );
    }

    /**
     * 미리보기
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Xpressengine\Keygen\UnknownGeneratorException
     */
    public function preview()
    {
        /** @var \Illuminate\Http\Request $request */
        $request = app('request');

        if ($this->permissionHandler->hasCreate($this->boardId) === false) {
            throw new AccessDeniedHttpException;
        }

        $user = Auth::user();

        // get rules
        /** @var \Xpressengine\Plugins\Board\Validator $validator */
        $validator = app('xe.board.validator');
        $rules = $validator->makeRule($this->config);
        if ($user instanceof Guest) {
            $rules = array_merge($rules, $validator->guestStore());
        }

        $this->validate($request, $rules);

        $doc = new DocumentEntity($this->handler->documentFilter($request->all()));
        $doc->id = 'preview-' . (new Keygen())->generate();
        $doc->instanceId = $this->boardId;
        $doc->createdAt = date('Y-m-d H:i:s');

        if ($user instanceof Guest) {
            $doc->setUserType($doc::USER_TYPE_GUEST);
        }

        $doc->setAuthor($user);

        $item = $this->handler->makeItem($doc);

        /** @var \Xpressengine\Storage\Storage $storage */
        if (($fileIds = $request->get('_files')) !== null) {
            $storage = app('xe.storage');
            $item->setFiles($storage->getsIn($fileIds));
        }

        $formColumns = $this->configHandler->formColumns($this->boardId);

        return Presenter::make('preview', [
            'config' => $this->config,
            'item' => $item,
            'handler' => $this->handler,
            'formColumns' => $formColumns,
        ]);
    }

    /**
     * delete document
     *
     * @param string $url url
     * @param string $id  document id
     * @return \Illuminate\Http\RedirectResponse|\Xpressengine\Presenter\RendererInterface
     */
    public function destroy($url, $id)
    {
        $item = $this->handler->get($id, $this->boardId);
        $doc = $item->getDocument();

        // 비회원이 작성 한 글 인증
        /** @var \Xpressengine\Plugins\Board\IdentifyManager $identifyManager */
        $identifyManager = app('xe.board.identify');
        if ($doc->isGuest() === true && $identifyManager->identified($doc) === false) {
            return $this->identify($doc);
        }

        XeDB::beginTransaction();

        // check permission
        if ($this->handler->remove($item, $this->config) !== 1) {
            XeDB::rollBack();
            throw new DeleteFailException;
        }

        XeDB::commit();

        $identifyManager->destroy($doc);

        // 어떤 리스틑 보여 줘야 하는지 계산...

        return Redirect::to($this->urlHandler->get('index', Input::except('id')));
    }

    /**
     * 휴지통 이동
     *
     * @return \Illuminate\Http\RedirectResponse|\Xpressengine\Presenter\RendererInterface
     */
    public function trash()
    {
        $id = Input::get('id');
        $author = Auth::user();

        $item = $this->handler->get($id, $this->boardId);

        // 관리자 또는 본인 글이 아니면 접근 할 수 없음
        if ($author->getRating() !== 'super' && $author->getId() != $item->id) {
            throw new NotFoundDocumentException;
        }

        $config = $this->configHandler->get($item->instanceId);
        $item = $this->handler->trash($item, $config);

        // post 로 처리하고.. 이전 페이지로.. 항상 ajax
        return Redirect::to($this->urlHandler->get('index'))->with(['alert' => ['type' => 'success', 'message' => '벌렸습니다.']]);
    }

    /**
     * show
     *
     * @param string $url url
     * @param string $id  id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function show($url, $id)
    {
        if ($this->permissionHandler->hasRead($this->boardId) === false) {
            throw new AccessDeniedHttpException;
        }

        /** @var \Xpressengine\Plugins\Board\Controllers\DataImporter $dataImporter */
        $dataImporter = app('xe.board.dataImporter')->init($this);

        // check short id generator
        /** @var \Xpressengine\Plugins\ShortIdGenerator\Plugin $shortIdGenerator */
        $shortIdGenerator = app('xe.plugin.shortIdGenerator');
        $shortIdEntity = $shortIdGenerator->get($id);
        if ($shortIdEntity !== null) {
            $id = $shortIdEntity->getOriginId();
        }

        return Presenter::make('show', array_merge($dataImporter->show($id), $dataImporter->index()));
    }

    /**
     * @param $boardId
     * @param $slug
     * @return \Xpressengine\Presenter\RendererInterface
     * @throws Exception
     */
    public function slug($boardId, $slug)
    {
        $slug = app('xe.board.slug')->find($slug);

        if ($slug === null) {
            throw new NotFoundDocumentException;
        }

        return $this->show($slug->instanceId, $slug->id);
    }

    /**
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function checkSlug()
    {
        /** @var \Xpressengine\Plugins\Board\SlugRepository $slugRepository */
        $slugRepository = app('xe.board.slug');
        $slug = $slugRepository->convert('', Input::get('slug'));
        $slug = $slugRepository->make($slug, Input::get('id'), $this->boardId);

        return Presenter::makeApi([
            'slug' => $slug,
        ]);
    }

    /**
     * @param $boardId
     * @param $id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function revision($boardId, $id)
    {
        $docs = $this->revisionHandler->getRevisions($id);

        $formColumns = $this->handler->formColumns($this->boardId);

        return Presenter::make('revision', [
            'config' => $this->config,
            'docs' => $docs,
            'handler' => $this->handler,
            'formColumns' => $formColumns,
        ]);
    }

    /**
     * 투표 정보
     *
     * @param $boardId
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function showVote($boardId)
    {
        // display 설정
        $display =['assent' => true, 'dissent' => true];
        if ($this->config->get('assent') !== true) {
            $display['assent'] = false;
        }

        if ($this->config->get('dissent') !== true) {
            $display['dissent'] = false;
        }

        $id = Input::get('id');
        $author = Auth::user();

        $voteHandler = app('xe.board.vote');
        $counts = $voteHandler->count($id);

        $vote = $voteHandler->get($id, $author);

        return Presenter::makeApi([
            'display' => $display,
            'id' => $id,
            'counts' => $counts,
            'voteAt' => $vote['counterOption'],
        ]);
    }

    /**
     * 찬성
     *
     * @param $boardId
     * @param $option
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function addVote($boardId, $option)
    {
        $id = Input::get('id');
        $author = Auth::user();

        $item = $this->handler->get($id, $this->boardId);

        $voteHandler = app('xe.board.vote');
        $voteHandler->add($item, $author, $option);

        return $this->showVote($this->boardId);
    }

    /**
     * 반대
     *
     * @param $boardId
     * @param $option
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function removeVote($boardId, $option)
    {
        $id = Input::get('id');
        $author = Auth::user();

        $item = $this->handler->get($id, $this->boardId);

        $voteHandler = app('xe.board.vote');
        $voteHandler->remove($item, $author, $option);

        return $this->showVote($this->boardId);
    }

    /**
     * get voted user list
     *
     * @param $boardId
     * @param $option
     */
    public function votedUsers($boardId, $option)
    {
        $id = Input::get('id');
        $author = Auth::user();

        $item = $this->handler->get($id, $this->boardId);

        $voteHandler = app('xe.board.vote');
        $paginator = $voteHandler->paginate($id, $option, Input::get('perPage'));

        $userIds = [];
        foreach ($paginator as $item) {
            $userIds[] = $item['userId'];
        }

        /** @var \Xpressengine\Member\Repositories\MemberRepositoryInterface $memberRepository */
        $memberRepository = app('Xpressengine\Member\Repositories\MemberRepositoryInterface');
        $users = $memberRepository->findAll($userIds);

        $userList = [];
        foreach ($users as $user) {
            $userList[] = [
                'id' => $user->id,
                'displayName' => $user->displayName,
                'profileImage' => $user->getProfileImage(),
            ];
        }

        return Presenter::makeApi([
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'users' => $userList
        ]);
    }



    /**
     * file upload
     *
     * @return string|\Xpressengine\Presenter\RendererInterface
     * @throws Exception
     * @throws \Xpressengine\Media\Exceptions\NotAvailableException
     * @throws \Xpressengine\Storage\Exceptions\InvalidFileException
     */
    public function fileUpload()
    {
        /** @var \Xpressengine\Storage\Storage $storage */
        $storage = app('xe.storage');

        $uploadedFile = null;
        if (Input::file('file') !== null) {
            $uploadedFile = Input::file('file');
        } elseif (Input::file('image') !== null) {
            $uploadedFile = Input::file('image');
        }

        if ($uploadedFile === null) {
            throw new \Exception;
        }

        $file = $storage->upload($uploadedFile, Board::FILE_UPLOAD_PATH);

        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = \App::make('xe.media');
        $media = null;
        $thumbnails = null;
        if ($mediaManager->is($file) === true) {
            $media = $mediaManager->make($file);
            $thumbnails = $mediaManager->createThumbnails($media, Board::THUMBNAIL_TYPE);

            $media = $media->toArray();

            if (!empty($thumbnails)) {
                $info['thumbnails'] = $thumbnails;
            }
        }

        return Presenter::makeApi([
            'file' => $file->toArray(),
            'media' => $media,
            'thumbnails' => $thumbnails,
        ]);
    }

    /**
     * get file's source
     *
     * @param string $url url
     * @param string $id  id
     * @return void
     */
    public function fileSource($url, $id)
    {
        $permission = $this->permissionHandler->get($this->boardId);
        if ($permission->unables(ACTION::READ) === true) {
            throw new AccessDeniedHttpException;
        }

        // permission 추가 해야 함.

        /** @var \Xpressengine\Storage\Storage $storage */
        $storage = app('xe.storage');
        $file = $storage->get($id);

        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = \App::make('xe.media');
        if ($mediaManager->is($file) === true) {
            /** @var \Xpressengine\Media\Handlers\ImageHandler $handler */
            $handler = $mediaManager->getHandler(\Xpressengine\Media\Spec\Media::TYPE_IMAGE);
            $dimension = 'L';
            if (\Agent::isMobile() === true) {
                $dimension = 'M';
            }

            $media = $handler->getThumbnail($mediaManager->make($file), Board::THUMBNAIL_TYPE, $dimension);
            $file = $media->getFile();
        }

        header('Content-type: ' . $file->mime);
        echo $storage->read($file);
    }

    /**
     * download file
     *
     * @param string $url url
     * @param string $id  id
     * @throws \Xpressengine\Storage\Exceptions\NotExistsException
     * @return void
     */
    public function fileDownload($url, $id)
    {
        $permission = $this->permissionHandler->get($this->boardId);
        if ($permission->unables(ACTION::READ) === true) {
            throw new AccessDeniedHttpException;
        }
        
        /** @var \Xpressengine\Storage\Storage $storage */
        $storage = app('xe.storage');
        $file = $storage->get($id);

        header('Content-type: ' . $file->mime);

        $storage->download($file);
    }

    /**
     * 해시태그 suggestion 리스트
     *
     * @param string $url url
     * @param string $id  id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function suggestionHashTag($url, $id = null)
    {
        /** @var \Xpressengine\Tag\TagHandler tag */
        $tag = \App::make('xe.tag');
        $terms = $tag->autoCompletion(\Input::get('string'));

        $suggestions = [];
        foreach ($terms as $tagEntity) {
            $suggestions[] = [
                'id' => $tagEntity->id,
                'word' => $tagEntity->word,
            ];
        }

        return Presenter::makeApi($suggestions);
    }

    /**
     * 멘션 suggestion 리스트
     *
     * @param string $url url
     * @param string $id  id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function suggestionMention($url, $id = null)
    {
        $userIds = [];

        // find in document/comments
        if ($id !== null) {
            /** @var \Xpressengine\Document\DocumentHandler $documentHandler */
            $documentHandler = app('xe.document');
            $doc = $documentHandler->get($id, 'bbbb');
            $userIds[] = $doc->userId;

            /** @var \Xpressengine\Comment\CommentHandler $commentHandler */
            $commentHandler = app('xe.comment');
            $comments = $commentHandler->gets(['targetId'=>$id]);
            foreach ($comments as $comment) {
                $userIds[] = $comment->userId;
            }
        }

        $string = Input::get('string');

        /** @var \Xpressengine\Member\Repositories\Database\MemberRepository $member */
        $member = app('xe.members');

        // 10개 안되면 전체 DB 에서 찾아보자
        if (count($userIds) < 10) {
            $users = $member->getConnection()->table('member')->whereNotIn('id', $userIds)
                ->where('displayName', 'like', $string . '%')->get(['id']);
            foreach ($users as $user) {
                $userIds[] = $user['id'];
            }
        }

        $users = $member->getConnection()->table('member')->whereIn('id', $userIds)
            ->where('displayName', 'like', $string . '%')->get(['id', 'displayName', 'profileImage']);

        foreach ($users as $user) {
            $key = array_search($user['id'], $userIds);
            if ($key !== null && $key !== false) {
                unset($userIds[$key]);
            }
        }

        // 본인은 안나오게 하자..
        $suggestions = [];
        foreach ($users as $user) {
            $suggestions[] = [
                'id' => $user['id'],
                'displayName' => $user['displayName'],
                'profileImage' => $user['profileImage'],
            ];
        }
        return Presenter::makeApi($suggestions);
    }
}
