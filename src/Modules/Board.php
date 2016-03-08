<?php
/**
 * Board
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
namespace Xpressengine\Plugins\Board\Modules;

use Illuminate\Database\Schema\Blueprint;
use Route;
use Skin;
use View;
use Xpressengine\Module\AbstractModule;
use Xpressengine\Plugins\Board\Models\BoardSlug;
use Xpressengine\Plugins\Board\ToggleMenus\TrashItem;

/**
 * # Board
 * * Board Module
 * * AbstractModule 인터페이스 지원. 메뉴로 추가할 수 있음.
 * * Boot 할 때 Addon, Order 게시판 번들 추가 기능 등록
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Board extends AbstractModule
{
    const FILE_UPLOAD_PATH = 'attached/board';
    const THUMBNAIL_TYPE = 'spill';

    /**
     * boot
     *
     * @return void
     */
    public static function boot()
    {
        self::registerManageRoute();
        self::registerInstanceRoute();
        self::registerToggleMenu();
        self::registerSettingsMenu();
    }

    /**
     * Register Plugin Manage Route
     *
     * @return void
     */
    protected static function registerManageRoute()
    {

        Route::settings(self::getId(), function () {
            Route::get('/', ['as' => 'manage.board.board.index', 'uses' => 'ManagerController@index']);
            Route::get(
                '/global/edit',
                ['as' => 'manage.board.board.global.edit', 'uses' => 'ManagerController@globalEdit']
            );
            Route::get('edit/{boardId}', ['as' => 'manage.board.board.edit', 'uses' => 'ManagerController@edit']);
            Route::post('storeCategory/{boardId}', ['as' => 'manage.board.board.storeCategory', 'uses' => 'ManagerController@storeCategory']);
            Route::post(
                'update/{boardId}',
                ['as' => 'manage.board.board.update', 'uses' => 'ManagerController@update']
            );
            Route::get('docs', [
                'as' => 'manage.board.board.docs.index',
                'uses' => 'ManagerController@docsIndex',
                'settings_menu' => 'contents.board.board'
            ]);
            Route::get('docs/trash', [
                'as' => 'manage.board.board.docs.trash',
                'uses' => 'ManagerController@docsTrash',
                'settings_menu' => 'contents.board.boardtrash'
            ]);
            Route::get('docs/approve', [
                'as' => 'manage.board.board.docs.approve',
                'uses' => 'ManagerController@docsApprove',
                'settings_menu' => 'contents.board.boardapprove'
            ]);
            Route::post('approve', ['as' => 'manage.board.board.approve', 'uses' => 'ManagerController@approve']);
            Route::post('copy', ['as' => 'manage.board.board.copy', 'uses' => 'ManagerController@copy']);
            Route::post('destroy', ['as' => 'manage.board.board.destroy', 'uses' => 'ManagerController@destroy']);
            Route::post('trash', ['as' => 'manage.board.board.trash', 'uses' => 'ManagerController@trash']);
            Route::post('move', ['as' => 'manage.board.board.move', 'uses' => 'ManagerController@move']);
            Route::post('restore', ['as' => 'manage.board.board.restore', 'uses' => 'ManagerController@restore']);
        }, ['namespace' => 'Xpressengine\Plugins\Board\Controllers']);
    }

    /**
     * Register Plugin Instance Route
     *
     * @return void
     */
    protected static function registerInstanceRoute()
    {
        Route::instance(self::getId(), function () {
            Route::get('/', ['as' => 'index', 'uses' => 'UserController@index']);
            Route::get('/create', ['as' => 'create', 'uses' => 'UserController@create']);
            Route::get('/edit/{id}', ['as' => 'edit', 'uses' => 'UserController@edit']);
            Route::get('/destroy/{id}', ['as' => 'destroy', 'uses' => 'UserController@destroy']);
            Route::get('/show/{id}', ['as' => 'show', 'uses' => 'UserController@show']);
            Route::get('/identify/{id}', ['as' => 'identify', 'uses' => 'UserController@identify']);
            Route::get('/revision/{id}', ['as' => 'revision', 'uses' => 'UserController@revision']);
            Route::post('/store', ['as' => 'store', 'uses' => 'UserController@store']);
            Route::post('/preview', ['as' => 'preview', 'uses' => 'UserController@preview']);
            Route::post('/temporary', ['as' => 'temporary', 'uses' => 'UserController@temporary']);
            Route::get('/trash', ['as' => 'trash', 'uses' => 'UserController@trash']);
            Route::post('/trash', ['as' => 'trash', 'uses' => 'UserController@trash']);
            Route::post('/identificationConfirm', ['as' => 'identificationConfirm', 'uses' => 'UserController@identificationConfirm']);
            Route::post('/update', ['as' => 'update', 'uses' => 'UserController@update']);
            Route::get('/vote/show', ['as' => 'showVote', 'uses' => 'UserController@showVote']);
            Route::get('/vote/users/{option}', ['as' => 'votedUsers', 'uses' => 'UserController@votedUsers']);
            Route::post('/vote/add/{option}', ['as' => 'addVote', 'uses' => 'UserController@addVote']);
            Route::post('/vote/remove/{option}', ['as' => 'removeVote', 'uses' => 'UserController@removeVote']);
            Route::post('/manageMenus/{id}', ['as' => 'manageMenus', 'uses' => 'UserController@manageMenus']);
            Route::get('/comment/list', ['as' => 'comment.index', 'uses' => 'UserController@pageCommentIndex']);
            Route::post('/comment/store', ['as' => 'comment.store', 'uses' => 'UserController@pageCommentStore']);
            Route::post('/comment/update', ['as' => 'comment.update', 'uses' => 'UserController@pageCommentUpdate']);
            Route::post('/comment/destroy', ['as' => 'comment.destroy', 'uses' => 'UserController@pageCommentDestroy']);
            Route::post('/file/upload', ['as' => 'upload', 'uses' => 'UserController@fileUpload']);
            Route::get('/file/source/{id}', ['as' => 'source', 'uses' => 'UserController@fileSource']);
            Route::get('/file/download/{id}', ['as' => 'download', 'uses' => 'UserController@fileDownload']);
            Route::get('/suggestion/hashTag/{id?}', ['as' => 'hashTag', 'uses' => 'UserController@suggestionHashTag']);
            Route::get('/suggestion/mention/{id?}', ['as' => 'mention', 'uses' => 'UserController@suggestionMention']);
            Route::get('/slug/{slug}', ['as' => 'slug2', 'uses' => 'UserController@slug']);
            Route::get('/hasSlug', ['as' => 'hasSlug', 'uses' => 'UserController@hasSlug']);
            Route::get('/{slug}', ['as' => 'slug', 'uses' => 'UserController@slug']);
        }, ['namespace' => 'Xpressengine\Plugins\Board\Controllers']);

        BoardSlug::setReserved([
            'index', 'create', 'edit', 'destroy', 'show', 'identify', 'revision', 'store', 'preview', 'temporary',
            'trash', 'certify', 'update', 'vote', 'manageMenus', 'comment', 'file', 'suggestion', 'slug', 'hasSlug'
        ]);
    }

    /**
     * Register toggle menu
     *
     * @return void
     */
    protected static function registerToggleMenu()
    {
        app('xe.pluginRegister')->add(TrashItem::class);
    }

    /**
     * register interception
     *
     * @return void
     */
    public static function registerSettingsMenu()
    {
        // settings menu 등록
        $menus = [
            'contents.board' => [
                'title' => '게시판',
                'display' => true,
                'description' => 'blur blur~',
                'ordering' => 2000
            ],
            'contents.board.board' => [
                'title' => '게시물 관리',
                'display' => true,
                'description' => 'blur blur~',
                'link' => route('manage.board.board.docs.index'),
                'ordering' => 2001
            ],
            'contents.board.boardapprove' => [
                'title' => '승인',
                'display' => true,
                'description' => 'blur blur~',
                'link' => route('manage.board.board.docs.approve'),
                'ordering' => 2002
            ],
            'contents.board.boardtrash' => [
                'title' => '휴지통',
                'display' => true,
                'description' => 'blur blur~',
                'link' => route('manage.board.board.docs.trash'), 'settings_menu' => 'contents.board.boardtrash',
                'ordering' => 2003
            ],
        ];
        foreach ($menus as $id => $menu) {
            app('xe.register')->push('settings/menu', $id, $menu);
        }
    }

    /**
     * get manage URI
     *
     * @return string
     */
    public static function getSettingsURI()
    {
        return route('manage.board.board.global.edit');
    }

    /**
     * this module is route able
     *
     * @return bool
     */
    public static function isRouteAble()
    {
        return true;
    }

    /**
     * Return Create Form View
     *
     * @return string
     */
    public function createMenuForm()
    {
        $skins = Skin::getList('module/board@board');

        return View::make('board::views/menuType/create', [
            'boardId' => null,
            'config' => app('xe.board.config')->getDefault(),
            'skins' => $skins,
            'handler' => app('xe.board.handler'),
        ])->render();
    }

    /**
     * Process to Store
     *
     * @param string $instanceId     instance id
     * @param array  $menuTypeParams menu type parameters
     * @param array  $itemParams     item parameters
     * @return void
     */
    public function storeMenu($instanceId, $menuTypeParams, $itemParams)
    {
        $input = $menuTypeParams;
        $input['boardId'] = $instanceId;
        if ($input['boardName'] == '') {
            $input['boardName'] = $itemParams['title'];
        }

        app('xe.board.instance')->create($input);
    }

    /**
     * Return Edit Form View
     *
     * @param string $instanceId instance id
     * @return string
     */
    public function editMenuForm($instanceId)
    {
        $skins = Skin::getList(self::getId());

        return View::make('board::views/menuType/edit', [
            'boardId' => $instanceId,
            'config' => app('xe.board.config')->get($instanceId),
            'skins' => $skins,
            'handler' => app('xe.board.handler'),
        ])->render();
    }

    /**
     * Process to Update
     *
     * @param string $instanceId     instance id
     * @param array  $menuTypeParams menu type parameters
     * @param array  $itemParams     item parameters
     * @return void
     */
    public function updateMenu($instanceId, $menuTypeParams, $itemParams)
    {
        $menuTypeParams['boardId'] = $instanceId;
        app('xe.board.instance')->updateConfig($menuTypeParams);
    }

    /**
     * Process to delete
     *
     * @param string $instanceId instance id
     * @return void
     */
    public function deleteMenu($instanceId)
    {
        app('xe.board.instance')->destroy($instanceId);
    }

    /**
     * summary
     *
     * @param string $instanceId instance id
     * @return string
     */
    public function summary($instanceId)
    {
        return xe_trans(
            'board::destroySummary',
            app('xe.board.instance')->summary($instanceId, app('xe.board.handler'))
        );
    }

    /**
     * Return URL about module's detail setting
     * getInstanceSettingURI
     *
     * @param string $instanceId instance id
     * @return mixed
     */
    public static function getInstanceSettingURI($instanceId)
    {
        return route('manage.board.board.edit', $instanceId);
    }
}
