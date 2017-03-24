<?php
/**
 * GalleryWidget
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
namespace Xpressengine\Plugins\Board\Widgets;

use Carbon\Carbon;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Models\BoardGalleryThumb;
use Xpressengine\Plugins\Board\UrlHandler;
use Xpressengine\Storage\File;
use Xpressengine\Widget\AbstractWidget;
use View;
use Xpressengine\Media\Models\Image;
use Xpressengine\Plugins\Board\Modules\BoardModule;
use Xpressengine\Menu\Models\MenuItem;

/**
 * GalleryWidget
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class GalleryWidget extends AbstractWidget
{
    /**
     * @var string
     */
    protected static $viewAlias = 'board::views.widgets.gallery';

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        $widgetConfig = $this->setting();

        /** @var \Xpressengine\Plugins\Board\Handler $boardHandler */
        $boardHandler = app('xe.board.handler');
        $configHandler = app('xe.board.config');
        $urlHandler = app('xe.board.url');

        $menuItem = MenuItem::find($widgetConfig['board_id']);

        $boardConfig = $configHandler->get($menuItem->id);
        $boardId = $boardConfig->get('boardId');
        $take = $widgetConfig['take'];
        $recent_date = (int)$widgetConfig['recent_date'];
        $orderType = $widgetConfig['order_type'];

        /**
         * config 할수 있는것
         * 몇개, 게시판 아이디, 최근 몇일, 정렬 방법
         */

        $model = Board::division($boardId);
        /** @var \Xpressengine\Database\DynamicQuery $query */
        $query = $model->where('instanceId', $boardId);
        $query = $query->leftJoin(
            'board_gallery_thumbs',
            sprintf('%s.%s', $query->getQuery()->from, 'id'),
            '=',
            sprintf('%s.%s', 'board_gallery_thumbs', 'targetId')
        );
        $query = $query->visible();

        //$recent_date
        if ($recent_date !== 0) {
            $current = Carbon::now();
            $query = $query->where(
                'createdAt',
                '>=',
                $current->addDay(-1 * $recent_date)->toDateString() . ' 00:00:00'
            )->where('createdAt', '<=', $current->addDay($recent_date)->toDateString() . ' 23:59:59');
        }

        //$orderType
        if ($orderType == '') {
            $query = $query->orderBy('head', 'desc');
        } elseif ($orderType == 'assentCount') {
            $query = $query->orderBy('assentCount', 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recentlyCreated') {
            $query = $query->orderBy(Board::CREATED_AT, 'desc')->orderBy('head', 'desc');
        } elseif ($orderType == 'recentlyUpdated') {
            $query = $query->orderBy(Board::UPDATED_AT, 'desc')->orderBy('head', 'desc');
        }

        $query = $query->take($take);

        $list = $query->get();

        foreach ($list as $item) {
            $thumbItem = BoardGalleryThumb::find($item->id);
            if ($thumbItem !== null) {
                $item->boardThumbnailFileId = $thumbItem->boardThumbnailFileId;
                $item->boardThumbnailExternalPath = $thumbItem->boardThumbnailExternalPath;
                $item->boardThumbnailPath = $thumbItem->boardThumbnailPath;
            }
        }
        $this->attachThumbnail($list);

        $urlHandler = new UrlHandler($boardConfig);

        return $view = View::make(sprintf('%s.widget', static::$viewAlias), [
            'list' => $list,
            'boardConfig' => $boardConfig,
            'menuItem' => $menuItem,
            'widgetConfig' => $widgetConfig,
            'urlHandler' => $urlHandler,
        ]);
    }

    /**
     * 위젯 설정 페이지에 출력할 폼을 출력한다.
     *
     * @param array $args 설정값
     *
     * @return string
     */
    public function renderSetting(array $args = [])
    {
        return $view = View::make(sprintf('%s.%s', static::$viewAlias, 'setting'), [
            'args' => $args,
            'boardList' => $this->getBoardList(),
        ]);
    }

    /**
     * attach thumbnail for list
     *
     * @param array $list list of board model
     * @return void
     */
    public function attachThumbnail($list)
    {
        foreach ($list as $item) {
            $this->bindGalleryThumb($item);
        }
    }

    /**
     * bind gallery thumbnail
     *
     * @param Board $item board model
     * @return void
     */
    protected function bindGalleryThumb(Board $item)
    {
        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = app('xe.media');

        // board gallery thumbnails 에 항목이 없는 경우
        if ($item->boardThumbnailFileId === null && $item->boardThumbnailPath === null) {
            // find file by document id
            $files = File::getByFileable($item->id);
            $fileId = '';
            $externalPath = '';
            $thumbnailPath = '';

            if (count($files) == 0) {
                // find file by contents link or path
                $externalPath = $this->getImagePathFromContent($item->content);

                // make thumbnail
                $thumbnailPath = $externalPath;
            } else {
                foreach ($files as $file) {
                    if ($mediaManager->is($file) !== true) {
                        continue;
                    }

                    /**
                     * set thumbnail size
                     */
                    $dimension = 'L';

                    $media = Image::getThumbnail(
                        $mediaManager->make($file),
                        BoardModule::THUMBNAIL_TYPE,
                        $dimension
                    );

                    if ($media === null) {
                        continue;
                    }

                    $fileId = $file->id;
                    $thumbnailPath = $media->url();
                    break;
                }
            }

            $item->boardThumbnailFileId = $fileId;
            $item->boardThumbnailExternalPath = $externalPath;
            $item->boardThumbnailPath = $thumbnailPath;

            $model = new BoardGalleryThumb;
            $model->fill([
                'targetId' => $item->id,
                'boardThumbnailFileId' => $fileId,
                'boardThumbnailExternalPath' => $externalPath,
                'boardThumbnailPath' => $thumbnailPath,
            ]);
            $model->save();
        }

        // 없을 경우 출력될 디폴트 이미지 (스킨의 설정으로 뺄 수 있을것 같음)
        if ($item->boardThumbnailPath == '') {
            $item->boardThumbnailPath = 'http://placehold.it/300x200';
        }
    }

    /**
     * get path from content image tag source
     *
     * @param string $content document content
     * @return string
     */
    protected function getImagePathFromContent($content)
    {
        $path = '';

        $pattern = '/<img[^>]*src="([^"]+)"[^>][^>]*>/';
        $matches = [];

        preg_match_all($pattern, $content, $matches);
        if (isset($matches[1][0])) {
            $path= $matches[1][0];
        }

        return $path;
    }

    /**
     * get board list
     *
     * @return array
     */
    protected function getBoardList()
    {
        $configHandler = app('xe.board.config');
        $boards = $configHandler->gets();
        $boardList = [];
        /** @var \Xpressengine\Config\ConfigEntity $config */
        foreach ($boards as $config) {
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
        return $boardList;
    }
}
