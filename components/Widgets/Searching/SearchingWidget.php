<?php

namespace Xpressengine\Plugins\Board\Components\Widgets\Searching;

use View;
use Illuminate\Support\Arr;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugin\SupportInfoTrait;
use Xpressengine\Plugins\Board\ConfigHandler as BoardConfigHandler;
use Xpressengine\Plugins\Board\UrlHandler as BoardUrlHandler;
use Xpressengine\Widget\AbstractWidget;

class SearchingWidget extends AbstractWidget
{
    use SupportInfoTrait;

    protected static $path = 'board/components/Widgets/Searching';

    /**
     * 그려줄 내용 반환.
     *
     * @return string
     */
    public function render()
    {
        /** @var BoardUrlHandler $urlHandler */
        $urlHandler = app(BoardUrlHandler::class);

        $widgetConfig = $this->setting();
        $boardMenuItem = MenuItem::where('type', 'board@board')->findOrFail(Arr::get($widgetConfig, 'board_id'));
        $boardConfig = BoardConfigHandler::make()->get($boardMenuItem->id);

        return $this->renderSkin([
            'urlHandler' => $urlHandler,
            'widgetConfig' => $widgetConfig,
            'boardMenuItem' => $boardMenuItem,
            'boardConfig' => $boardConfig
        ]);
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

        return View::make('board::components.Widgets.Searching.views.settings', [
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