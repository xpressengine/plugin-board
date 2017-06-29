<?php
namespace Xpressengine\Plugins\Board\Skins;

use Xpressengine\Plugins\Board\Skins\GenericBoardSkin;
use View;
use XeFrontend;
use XeRegister;
use XePresenter;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugins\Board\Skins\DynamicField\DesignSelectSkin;
use Xpressengine\Plugins\Board\Skins\Pagination\MobilePresenter;
use Xpressengine\Plugins\Board\Skins\Pagination\BasePresenter;
use Xpressengine\Presenter\Presenter;
use Xpressengine\Routing\InstanceConfig;

class DefaultSkin extends GenericBoardSkin
{
    protected static $path = 'board/components/board_skins/default';

    /**
     * @var array
     */
    protected $defaultListColumns = [
        'title', 'writer', 'assentCount', 'readCount', 'createdAt', 'updatedAt', 'dissentCount',
    ];

    protected $defaultSelectedListColumns = [
        'title', 'writer',  'assentCount', 'readCount', 'createdAt',
    ];

    /**
     * @var array
     */
    protected $defaultFormColumns = [
        'title', 'content',
    ];

    /**
     * @var array
     */
    protected $defaultSelectedFormColumns = [
        'title', 'content',
    ];

    /**
     * render
     *
     * @return \Illuminate\Contracts\Support\Renderable|string
     */
    public function render()
    {
        $this->setSkinConfig();
        $this->setDynamicFieldSkins();
        $this->setPaginationPresenter();
        $this->setBoardList();
        $this->setTerms();

        // 스킨 view(blade)파일이나 js 에서 사용할 다국어 정의
        XeFrontend::translation([
            'board::selectPost',
            'board::selectBoard',
            'board::msgDeleteConfirm',
        ]);

        return parent::render();
    }

    /**
     * set skin config to data
     *
     * @return void
     */
    protected function setSkinConfig()
    {
        // 기본 설정
        if (empty($this->config['listColumns'])) {
            $this->config['listColumns'] = $this->defaultSelectedListColumns;
        }
        if (empty($this->config['formColumns'])) {
            $this->config['formColumns'] = $this->defaultSelectedFormColumns;
        }
        $this->data['skinConfig'] = $this->config;
    }

    /**
     * replace dynamicField skins
     *
     * @return void
     */
    protected function setDynamicFieldSkins()
    {
        // replace dynamicField skin registered information
        XeRegister::set('fieldType/xpressengine@Category/fieldSkin/xpressengine@default', DesignSelectSkin::class);
    }

    /**
     * set pagination presenter
     * 스킨에서 추가한 만든 pagination presenter 사용
     *
     * @return void
     * @see views/defaultSkin/index.blade.php
     */
    protected function setPaginationPresenter()
    {
        if (isset($this->data['paginate'])) {
            $this->data['paginate']->setPath($this->data['urlHandler']->get('index'));
            $this->data['paginationPresenter'] = new BasePresenter($this->data['paginate']);
            $this->data['paginationMobilePresenter'] = new MobilePresenter($this->data['paginate']);
        }
    }

    /**
     * set board list (for supervisor)
     *
     * @return void
     */
    protected function setBoardList()
    {
        $instanceConfig = InstanceConfig::instance();
        $instanceId = $instanceConfig->getInstanceId();

        $configHandler = app('xe.board.config');
        $boards = $configHandler->gets();
        $boardList = [];
        /** @var ConfigEntity $config */
        foreach ($boards as $config) {
            // 현재의 게시판은 리스트에서 제외
            if ($instanceId === $config->get('boardId')) {
                continue;
            }

            $boardName = $config->get('boardName');
            if ($boardName === null || $boardName === '') {
                $menuItem = MenuItem::find($config->get('boardId'));
                $boardName = $menuItem->title;
            }

            $boardList[] = [
                'value' => $config->get('boardId'),
                'text' => $boardName,
            ];
        }
        $this->data['boardList'] = $boardList;
    }

    /**
     * set terms for search select box list
     *
     * @return array
     */
    protected function setTerms()
    {
        $this->data['terms'] = [
            ['value' => '1week', 'text' => 'board::1week'],
            ['value' => '2week', 'text' => 'board::2week'],
            ['value' => '1month', 'text' => 'board::1month'],
            ['value' => '3month', 'text' => 'board::3month'],
            ['value' => '6month', 'text' => 'board::6month'],
            ['value' => '1year', 'text' => 'board::1year'],
        ];
    }
}
