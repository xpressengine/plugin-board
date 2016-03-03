<?php
/**
 * UserController
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
namespace Xpressengine\Plugins\Board\Controllers;

use App\Facades\XeDocument;
use App\Facades\Presenter;
use Auth;
use Gate;
use Storage;
use Frontend;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Xpressengine\Category\CategoryItem;
use Xpressengine\Counter\Counter;
use Xpressengine\Counter\Exceptions\GuestNotSupportException;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Media\Models\Image;
use Xpressengine\Member\Entities\MemberEntityInterface;
use Xpressengine\Permission\Instance;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Exceptions\InvalidIdentifyException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundDocumentException;
use Xpressengine\Plugins\Board\Exceptions\NotFoundUploadFileException;
use Xpressengine\Plugins\Board\Exceptions\RequiredValueException;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Modules\Board as BoardModule;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Plugins\Board\Validator;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\Storage\File;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;


/**
 * UserController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class UserController extends Controller
{
    /**
     * @var string
     */
    protected $instanceId;

    /**
     * @var Handler
     */
    public $handler;

    /**
     * @var ConfigHandler
     */
    public $configHandler;

    /**
     * @var UrlHandler
     */
    public $urlHandler;

    /**
     * @var ConfigEntity
     */
    public $config;

    /**
     * @var bool
     */
    public $isManager = false;

    /**
     * UserController constructor.
     * @param Handler $handler
     * @param ConfigHandler $configHandler
     * @param UrlHandler $urlHandler
     * @param BoardPermissionHandler $permissionHandler
     */
    //public function __construct(Handler $handler, ConfigHandler $configHandler, UrlHandler $urlHandler, BoardPermissionHandler $permissionHandler)
    public function __construct(Handler $handler, ConfigHandler $configHandler, UrlHandler $urlHandler)
    {
        $instanceConfig = InstanceConfig::instance();
        $this->instanceId = $instanceConfig->getInstanceId();

        $this->handler = $handler;
        $this->configHandler = $configHandler;
        $this->urlHandler = $urlHandler;

        $this->config = $configHandler->get($this->instanceId);
        $urlHandler->setConfig($this->config);

        //$this->isManager = $permissionHandler->isManager(Auth::guest(), $this->instanceId);
        $this->isManager = true;

        // set Skin
        Presenter::setSkin(BoardModule::getId());
        Presenter::share('handler', $handler);
        Presenter::share('configHandler', $configHandler);
        Presenter::share('urlHandler', $urlHandler);
        Presenter::share('isManager', $this->isManager);
        Presenter::share('instanceId', $this->instanceId);
        Presenter::share('config', $this->config);
    }

    /**
     * index
     *
     * @param Request           $request    request
     * @param PermissionHandler $permission board permission handler
     * @return \Xpressengine\Presenter\RendererInterface
     * @throws AccessDeniedHttpException
     */
    //public function index(Request $request, PermissionHandler $permission)
    public function index(Request $request, BoardPermissionhandler $boardPermission)
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_LIST,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        return Presenter::makeAll('index', $this->listDataImporter($request));
    }

    /**
     * get list data
     *
     * @param Request $request request
     * @return array
     */
    protected function listDataImporter(Request $request)
    {
        $query = $this->handler->getModel($this->config)->where('instanceId', $this->instanceId)
        ->where('status', Document::STATUS_PUBLIC)
        ->where('display', Document::DISPLAY_VISIBLE)
        ->where('published', Document::PUBLISHED_PUBLISHED);

        $query = $this->handler->makeWhere($query, $request);
        $query = $this->handler->makeOrder($query, $request);

        $paginate = $query->paginate($this->config->get('perPage'))->appends($request->except('page'));

        $fieldTypes = (array)$this->configHandler->getDynamicFields($this->config);

        $boardOrders = [];

        return compact('notices', 'paginate', 'fieldTypes', 'boardOrders');
    }

    /**
     * show
     * @param Request $request
     * @param BoardPermissionhandler $permission
     * @param $id
     * @return mixed
     */
    //public function show(Request $request, PermissionHandler $permission, $id)
    public function show(Request $request, BoardPermissionhandler $boardPermission, $id)
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_READ,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        return Presenter::make('show', array_merge($this->showDataImporter($id), $this->listDataImporter($request)));
    }

    protected function showDataImporter($id)
    {
        /** @var MemberEntityInterface $user */
        $user = Auth::user();
        /** @var Board $item */
        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        $visible = false;
        if ($item->display == Document::DISPLAY_VISIBLE) {
            $visible = true;
        }
        if ($item->display == Document::DISPLAY_SECRET) {
            if ($this->isManager == true) {
                $visible = true;
            } elseif ($user->getId() == $item->getAuthor()->getId()) {
                $visible = true;
            }
        }

        if ($visible === true) {
            $this->handler->incrementReadCount($item, $user);
        }

        $formColumns = $this->configHandler->formColumns($this->instanceId);

        return compact('item', 'visible', 'formColumns', 'boardOrders');
    }

    /**
     * @param $boardId
     * @param $slug
     * @return \Xpressengine\Presenter\RendererInterface
     * @throws Exception
     */
    //public function slug(Request $request, PermissionHandler $permission, $strSlug)
    public function slug(Request $request, $strSlug)
    {
        $slug = BoardSlug::where('slug', $strSlug)->first();

        if ($slug === null) {
            throw new NotFoundDocumentException;
        }

        //return $this->show($request, $permission, $slug->targetId);
        return $this->show($request, $slug->targetId);
    }

    //public function create(Request $request, PermissionHandler $permission, Validator $validator)
    public function create(Request $request, Validator $validator, BoardPermissionHandler $boardPermission)
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_CREATE,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        $parentId = '';
        $head = '';
        if ($request->get('parentId') != null) {
            $item = $this->handler->getModel($this->config)->find($request->get('parentId'));
            $parentId = $item->id;
            $head = $item->head;
        }

        /** @var MemberEntityInterface $user */
        $user = Auth::user();
        $rules = $validator->getCreateRule($user, $this->config);

        return Presenter::makeAll('create', [
            'action' => 'create',
            'handler' => $this->handler,
            'parentId' => $parentId,
            'head' => $head,
            'rules' => $rules,
        ]);
    }

    //public function store(Request $request, PermissionHandler $permission)
    public function store(Request $request, Validator $validator, BoardPermissionHandler $boardPermission)
    {
        if (Gate::denies(
            BoardPermissionHandler::ACTION_CREATE,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        $user = Auth::user();

        $this->validate($request, $validator->getCreateRule($user, $this->config));

        $inputs = $request->all();
        $inputs['instanceId'] = $this->instanceId;
        $inputs['content'] = $request->originAll()['content'];
        $inputs['title'] = htmlspecialchars($request->originAll()['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);

        if ($request->get('status') == 'notice' && $this->isManager) {
            $inputs['status'] = null;
        }

        // 암호 설정
        /** @var \Xpressengine\Plugins\Board\IdentifyManager $identifyManager */
        $identifyManager = app('xe.board.identify');
        if (empty($inputs['certifyKey']) === false) {
            $inputs['certifyKey'] = $identifyManager->hash($inputs['certifyKey']);
        }

        $board = $this->handler->add($inputs, $user);

        // 답글인 경우 부모글이 있는 곳으로 이동한다.(최대한..)
        if ($request->get('parentId') != '') {
            return Redirect::to(
                $this->urlHandler->get('index', $this->urlHandler->queryStringToArray($request->get('queryString')))
            );
        } else {
            return Redirect::to($this->urlHandler->get('index'));
        }
    }

    public function hasSlug(Request $request)
    {
        $slug = BoardSlug::convert('', $request->get('slug'));
        $slug = BoardSlug::make($slug, $request->get('id'), $this->instanceId);

        return Presenter::makeApi([
            'slug' => $slug,
        ]);
    }

    /**
     * edit
     *
     * @param string $url url
     * @param string $id  document id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    //public function edit(Request $request, PermissionHandler $permission, Validator $validator, $id)
    public function edit(Request $request, Validator $validator, BoardPermissionHandler $boardPermission, $id)
    {
        $user = Auth::user();

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        if ($item === null) {
            throw new NotFoundDocumentException;
        }

        // 비회원이 작성 한 글일 때 인증페이지로 이동
        /** @var \Xpressengine\Plugins\Board\IdentifyManager $identifyManager */
        $identifyManager = app('xe.board.identify');
        if (
            $item->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            $user->getRating() != 'super'
        ) {
            return $this->identify($item);
        }

        // 접근 권한 확인
        if (Gate::denies(
            BoardPermissionHandler::ACTION_CREATE,
            new Instance($boardPermission->name($this->instanceId)))
        ) {
            throw new AccessDeniedHttpException;
        }

        /** @var \Xpressengine\Plugins\Board\Validator $validator */
        $validator = app('xe.board.validator');
        $rules = $validator->getEditRule($user, $this->config);

        $parent = null;

        //$formColumns = $this->configHandler->formColumns($this->instanceId);

        return Presenter::make('edit', [
            'config' => $this->config,
            'handler' => $this->handler,
            'item' => $item,
            'parent' => $parent,
            //'formColumns' => $formColumns,
            'rules' => $rules,
        ]);
    }

    /**
     * update
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    //public function update(Request $request, PermissionHandler $permission)
    public function update(Request $request, Validator $validator)
    {
        $user = Auth::user();
        $id = $request->get('id');

        if ($id === null) {
            throw new RequiredValueException;
        }

        // 글 수정 시 게시판 설정이 아닌 글의 상태에 따른 처리가 되어야 한다.
        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        // 비회원이 작성 한 글 인증
        // 비회원이 작성 한 글일 때 인증페이지로 이동
        /** @var \Xpressengine\Plugins\Board\IdentifyManager $identifyManager */
        $identifyManager = app('xe.board.identify');
        if (
            $item->isGuest() === true &&
            $identifyManager->identified($item) === false &&
            $user->getRating() != 'super'
        ) {
            throw new InvalidIdentifyException;
        }

        $rules = $validator->getEditRule($user, $this->config);
        $this->validate($request, $rules);

        $inputs = $request->all();
        // replace purifying content to origin content value
        $inputs['content'] = $request->originAll()['content'];
        $inputs['title'] = htmlspecialchars($request->originAll()['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8', false);

        // 공지
        if ($request->get('status') == 'notice' && $this->isManager) {
            $item->status = Board::STATUS_NOTICE;
        }

        // 암호 설정
        if ($item->certifyKey != '') {
            $item->certifyKey = $identifyManager->hash($item->certifyKey);
        }

        // 비회원 글 수정시 비밀번호를 입력 안한 경우 원래 비밀번호로 설
        if ($item->getOriginal('certifyKey') != '' && $item->certifyKey == '') {
            $item->certifyKey = $item->getOriginal('certifyKey');
        }

        $board = $this->handler->put($item, $inputs);

        // 비회원 비밀번호를 변경 한 경우 세션 변경
        if ($item->getOriginal('certifyKey') != '' && $item->getOriginal('certifyKey') != $item->certifyKey) {
            $identifyManager->destroy($item);
            $identifyManager->create($item);
        }

        return Redirect::to(
            $this->urlHandler->getSlug($item->boardSlug->slug, $this->urlHandler->queryStringToArray($request->get('queryString')))
        );
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
     * 투표 정보
     *
     * @param $boardId
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function showVote(Request $request)
    {
        // display 설정
        $display =['assent' => true, 'dissent' => true];
        if ($this->config->get('assent') !== true) {
            $display['assent'] = false;
        }

        if ($this->config->get('dissent') !== true) {
            $display['dissent'] = false;
        }

        $id = $request->get('id');
        $user = Auth::user();

        $board = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($board, $this->config);

        $voteCounter = $this->handler->getVoteCounter();
        $vote = $voteCounter->getByName($id, $user);

        return Presenter::makeApi([
            'display' => $display,
            'id' => $id,
            'counts' => [
                'assent' => $board->assentCount,
                'dissent' => $board->dissentCount,
            ],
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
    public function addVote(Request $request, $option)
    {
        $id = $request->get('id');
        $author = Auth::user();

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        try {
            $this->handler->incrementVoteCount($item, $author, $option);
        } catch (GuestNotSupportException $e) {
            throw new AccessDeniedHttpException;
        }


        return $this->showVote($request);
    }

    /**
     * 반대
     *
     * @param $boardId
     * @param $option
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function removeVote(Request $request, $option)
    {
        $id = $request->get('id');
        $author = Auth::user();

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        $this->handler->decrementVoteCount($item, $author, $option);

        return $this->showVote($request);
    }

    /**
     * get voted user list
     *
     * @param $boardId
     * @param $option
     */
    public function votedUsers(Request $request, $option)
    {
        $id = $request->get('id');
        $author = Auth::user();

        $item = $this->handler->getModel($this->config)->find($id);
        $this->handler->setModelConfig($item, $this->config);

        $users = $this->handler->getVoteCounter()->getUsers($item->id, $request->get('perPage'), $option);

        $userList = [];
        foreach ($users as $user) {
            $userList[] = [
                'id' => $user->id,
                'displayName' => $user->displayName,
                'profileImage' => $user->getProfileImage(),
            ];
        }

        return Presenter::makeApi([
            'current_page' => $request->get('page'),
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
    public function fileUpload(Request $request)
    {
        /** @var \Xpressengine\Storage\Storage $storage */
        $storage = app('xe.storage');

        $uploadedFile = null;
        if ($request->file('file') !== null) {
            $uploadedFile = $request->file('file');
        } elseif ($request->file('image') !== null) {
            $uploadedFile = $request->file('image');
        }

        if ($uploadedFile === null) {
            throw new NotFoundUploadFileException;
        }

        $file = $storage->upload($uploadedFile, BoardModule::FILE_UPLOAD_PATH);

        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = \App::make('xe.media');
        $media = null;
        $thumbnails = null;
        if ($mediaManager->is($file) === true) {
            $media = $mediaManager->make($file);
            $thumbnails = $mediaManager->createThumbnails($media, BoardModule::THUMBNAIL_TYPE);

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
    public function fileSource($id)
    {
//        $permission = $this->permissionHandler->get($this->boardId);
//        if ($permission->unables(ACTION::READ) === true) {
//            throw new AccessDeniedHttpException;
//        }

        // permission 추가 해야 함.

        /** @var \Xpressengine\Storage\Storage $storage */
        $storage = app('xe.storage');
        $file = File::find($id);

        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = \App::make('xe.media');
        if ($mediaManager->is($file) === true) {
            $dimension = 'L';
            if (\Agent::isMobile() === true) {
                $dimension = 'M';
            }
            $media = Image::getThumbnail(
                $mediaManager->make($file),
                BoardModule::THUMBNAIL_TYPE,
                $dimension
            );

            $file = $media[0];
        }

        header('Content-type: ' . $file->mime);
        echo $file->getContent();
    }
}
