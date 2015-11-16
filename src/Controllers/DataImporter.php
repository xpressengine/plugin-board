<?php
/**
 * DataImporter
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

use App\Http\Controllers\Controller;
use Xpressengine\Document\DocumentEntity;
use Input;
use Auth;
use Storage;
use Xpressengine\Plugins\Board\Addon\AddonManager;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Exceptions\NotFoundDocumentException;
use Xpressengine\Plugins\Board\Order\OrderManager;

/**
 * DataImporter
 *
 * * Controller 에서 코드 재사용을 하기 위한 class
 * * Controller 데이터 생성 코드 제공
 * * 재사용을 고려해야하는 controller 코드를
 * 이곳에 작성하고 Controller 에서 사용하는 방식
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class DataImporter
{
    /**
     * @var UserController|ManagerController
     */
    protected $controller;

    /**
     * create instance
     */
    public function __construct()
    {
    }

    /**
     * init controller
     *
     * @param Controller|ManagerController|UserController $controller user or manager controller
     * @return $this
     */
    public function init(Controller $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * 게시판 리스트(/index) 데이터 제공
     *
     * @return array
     */
    public function index()
    {
        /** @var UserController $controller */
        $controller = $this->controller;

        $wheres = [
            'instanceId' => $controller->boardId,
            'status' => DocumentEntity::STATUS_PUBLIC,
            'display' => DocumentEntity::DISPLAY_VISIBLE,
            'published' => DocumentEntity::PUBLISHED_PUBLISHED,
        ];

        // keyword 검색 처리
        if (Input::get('searchKeyword') != '') {
            $searchTarget = Input::get('searchTarget');
            $searchKeyword = Input::get('searchKeyword');
            if ($searchTarget == 'title_content') {
                $wheres[$searchTarget] = $searchKeyword;
            } else {
                $wheres[$searchTarget] = $searchKeyword;
            }
        }

        // 상세 검색 처리
        foreach (Input::all() as $key => $value) {
            if ($value != '') {
                $wheres[$key] = $value;
            }
        }

        // 정렬 처리
        $orders =[];
        if (Input::get('orderType') != '' && Input::get('orderColumn') != '') {
            $orders = [Input::get('orderColumn') => Input::get('orderType')];
        }

        /** @var OrderManager $orderManager */
        $orderManager = app('xe.board.order');
        $boardOrders = $orderManager->gets();

        $orderManager->make($controller->config, Input::all(), $wheres, $orders);

        $paginate = $controller->handler->paginate($wheres, $orders, $controller->config)
            ->appends(Input::except('page'));

        /** @var ConfigHandler $configHandler */
        $configHandler = app('xe.board.config');
        $fieldTypes = (array)$configHandler->getDynamicFields($controller->config);

        // 지원하는 정렬 방식
        return compact('notices', 'paginate', 'fieldTypes', 'boardOrders');
    }

    /**
     * 게시판 조회(/show) 데이터 제공
     *
     * @param string $id document id
     * @return array
     */
    public function show($id)
    {
        /** @var UserController $controller */
        $controller = $this->controller;

        $user = Auth::user();
        $item = $controller->handler->get($id, $controller->boardId);

        $visible = false;
        if ($item->display == 'visible') {
            $visible = true;
        }
        if ($item->display == 'secret') {
            if ($controller->isManager == true) {
                $visible = true;
            } elseif ($user->getId() == $item->getAuthor()->getId()) {
                $visible = true;
            }
        }

        if ($visible === true) {
            // set files to board item entity
            $files = Storage::getsByTargetId($item->id);
            $item->setFiles($files);

            // 조회수 증가
            $readCounter = app('xe.board.readCounter');
            $readCounter->add($item, $user);
        }

        return [
            'config' => $controller->config,
            'item' => $item,
            'visible' => $visible,
            'handler' => $controller->handler,
            'formColumns' => $controller->configHandler->formColumns($controller->boardId),
        ];
    }
}
