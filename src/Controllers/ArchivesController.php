<?php
/**
 * ArchivesController
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
namespace Xpressengine\Plugins\Board\Controllers;

use Auth;
use Gate;
use Event;
use XePresenter;
use App\Http\Controllers\Controller;
use Xpressengine\Http\Request;
use Xpressengine\Permission\Instance;
use Xpressengine\Plugins\Board\BoardPermissionHandler;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Handler;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Plugins\Board\Services\BoardService;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Routing\InstanceConfig;

/**
 * ArchivesController
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class ArchivesController extends Controller
{
    /**
     * show document
     *
     * @param BoardService $service board service
     * @param Request      $request request
     * @param string       $slug    slug
     * @return mixed
     */
    public function index(BoardService $service, Request $request, $slug)
    {
        $slug = BoardSlug::where('slug', $slug)->first();

        $instanceId = $slug->instance_id;
        $id = $slug->target_id;

        $instanceConfig = InstanceConfig::instance();
        $instanceConfig->setInstanceId($slug->instance_id);

        /**
         * @var Handler $handler
         * @var ConfigHandler $configHandler
         * @var UrlHandler $urlHandler
         * @var BoardPermissionHandler $permission
         */
        $handler = app('xe.board.handler');
        $configHandler = app('xe.board.config');
        $urlHandler = app('xe.board.url');
        $permission = app('xe.board.permission');

        $config = $configHandler->get($instanceId);

        $isManager = false;
        if ($config !== null) {
            $urlHandler->setInstanceId($config->get('boardId'));
            $urlHandler->setConfig($config);

            $isManager = false;
            if (Gate::allows(
                BoardPermissionHandler::ACTION_MANAGE,
                new Instance($permission->name($instanceId))
            )) {
                $isManager = true;
            };
        }

        // set Skin
        XePresenter::setSkinTargetId(BoardModule::getId());
        XePresenter::share('handler', $handler);
        XePresenter::share('configHandler', $configHandler);
        XePresenter::share('urlHandler', $urlHandler);
        XePresenter::share('isManager', $isManager);
        XePresenter::share('instanceId', $instanceId);
        XePresenter::share('config', $config);
        
        $this->setCurrentPage($request, $configHandler, $slug);

        $item = $service->getItem($id, Auth::user(), $config, $isManager);

        // 글 조회수 증가
        if ($item->display == Board::DISPLAY_VISIBLE) {
            $handler->incrementReadCount($item, Auth::user());
        }

        $notices = $service->getNoticeItems($request, $config, Auth::user()->getId());
        $paginate = $service->getItems($request, $config, $id);
        $fieldTypes = $service->getFieldTypes($config);
        $categories = $service->getCategoryItems($config);
        $searchOptions = $service->getSearchOptions($request);

        return XePresenter::make('show', [
            'item' => $item,
            'notices' => $notices,
            'paginate' => $paginate,
            'categories' => $categories,
            'fieldTypes' => $fieldTypes,
            'searchOptions' => $searchOptions,
        ]);
    }

    /**
     * set current page
     *
     * @param Request       $request       request
     * @param ConfigHandler $configHandler config handler
     * @param BoardSlug     $slug          slug model
     * @return void
     */
    protected function setCurrentPage(
        Request $request,
        ConfigHandler $configHandler,
        BoardSlug $slug
    ) {
        $instanceId = $slug->instance_id;

        // 이 slug 가 포함된 페이지 출력
        $config = $configHandler->get($instanceId);
        /** @var Board $model */
        $model = Board::division($instanceId);
        $query = $model->where('instance_id', $instanceId)->visible();

        $orderType = $request->get('order_type', '');
        if ($orderType === '' && $config->get('order_type') != null) {
            $orderType = $config->get('order_type', '');
        }

        if ($orderType == '') {
            $query->where('head', '>=', $slug->board->head);
        } elseif ($orderType == 'assent_count') {
            $query->where('assent_count', '>', $slug->board->assent_count)
                ->orWhere(function ($query) use ($slug) {
                    $query->where('assent_count', '=', $slug->board->assent_count);
                    $query->where('head', '>=', $slug->board->head);
                });
        } elseif ($orderType == 'recently_created') {
            $query->where(Board::CREATED_AT, '>', $slug->board->{Board::CREATED_AT})
                ->orWhere(function ($query) use ($slug) {
                    $query->where(Board::CREATED_AT, '=', $slug->board->{Board::CREATED_AT});
                    $query->where('head', '>=', $slug->board->head);
                });
        } elseif ($orderType == 'recently_updated') {
            $query->where(Board::UPDATED_AT, '>', $slug->board->{Board::UPDATED_AT})
                ->orWhere(function ($query) use ($slug) {
                    $query->where(Board::UPDATED_AT, '=', $slug->board->{Board::UPDATED_AT});
                    $query->where('head', '>=', $slug->board->head);
                });
        }

        Event::fire('xe.plugin.board.archive', [$query, $slug->board]);
        $count = $query->count() ? : 1;

        $page = (int)($count / $config->get('perPage'));
        if ($count % $config->get('perPage') != 0) {
            ++$page;
        }
        $request->query->set('page', $page);
    }
}
