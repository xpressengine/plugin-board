<?php

namespace Xpressengine\Plugins\Board\Components\Widgets\QnaList;

use View;
use Illuminate\Support\Arr;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Http\Request;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugin\SupportInfoTrait;
use Xpressengine\Plugins\Board\{ConfigHandler, ReplyConfigHandler, Services\BoardService, UrlHandler};
use Xpressengine\Widget\AbstractWidget;

class QnaListWidget extends AbstractWidget
{
    use SupportInfoTrait;

    protected static $path = 'board/src/Widgets/QnaList';

   /**
     * render
     *
     * @return string
     */
    public function render(): string
    {
        $widgetConfig = $this->setting();
        $boardId = Arr::get($widgetConfig, 'board_id');
        $boardMenuItem = MenuItem::where('type', 'board@board')->findOrFail($boardId);

        $boardRequest = $this->getBoardRequest($widgetConfig);
        $boardConfig = $this->getBoardConfig($boardMenuItem, Arr::get($widgetConfig, 'take'));

        $boards = app(BoardService::class)->getItems($boardRequest, $boardConfig)->getCollection();
        $moreUrl = Arr::get($widgetConfig, 'using_more') !== 'true' ? null : app(UrlHandler::class)->get('index', $boardRequest->all(), $boardId);

        return $this->renderSkin([
            'widgetConfig' => $widgetConfig,
            'boards' => $boards,
            'moreUrl' => $moreUrl,
        ]);
    }

    /**
     * get board's request
     *
     * @param array $config
     * @return Request
     */
    private function getBoardRequest(array $config): Request
    {
        $request = new Request;

        if ($adoptFilter = Arr::get($config, 'adopt_filter')) {
            $key = null;
            $key = $adoptFilter === 'only_adopted' ? 'has_adopted' : $key;
            $key = $adoptFilter === 'only_unAdopted' ? 'has_not_adopted' : $key;

            if ($key !== null) {
                $request->merge([$key => '']);
            }
        }

        if ($orderType = Arr::get($config, 'order_type')) {
            $request->merge(['order_type' => $orderType]);
        }

        return $request;
    }

    /**
     * get board's config
     *
     * @param MenuItem $boardMenuItem
     * @param int|null $take
     * @return ConfigEntity
     */
    private function getBoardConfig(MenuItem $boardMenuItem, int $take = null): ConfigEntity
    {
        $boardConfig = ConfigHandler::make()->get($boardMenuItem->id);
        $replyConfig = ReplyConfigHandler::make()->getActivated($boardMenuItem->id);

        if (is_null($replyConfig)) {
            throw new \LogicException("not found reply config");
        }

        if ($take !== null) {
            $boardConfig->set('perPage', $take);
            $boardConfig->set('pageCount', $take);
        }

        return $boardConfig;
    }

    /**
     * render settings
     *
     * @param array $args
     * @return string
     */
    public function renderSetting(array $args = []): string
    {
        $boards = $this->getBoards();

        return View::make('board::components.Widgets.QnaList.views.settings', [
            'args' => $args,
            'boards' => $boards
        ]);
    }

    /**
     * get boards
     *
     * @return array
     */
    protected function getBoards(): array
    {
        return MenuItem::where('type', 'board@board')
            ->where('activated', true)
            ->whereIn('id', ReplyConfigHandler::make()->getActivatedIds())
            ->get()
            ->map(function(MenuItem $menuItem) {
                return [
                    'id' => $menuItem->id,
                    'text' => $menuItem->title,
                ];
            })
            ->toArray();
    }
}
