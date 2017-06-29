<?php
namespace Xpressengine\Plugins\Board\Skins;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\Http\Request;
use Xpressengine\Media\Models\Image;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Models\BoardGalleryThumb;
use Xpressengine\Plugins\Board\Handler as BoardHandler;
use Xpressengine\Storage\File;
use Xpressengine\Plugins\Board\Modules\BoardModule;
use XeSkin;
use XePresenter;
use View;
use Event;
use Input;
use App;

class GallerySkin extends DefaultSkin
{
    protected static $path = 'board/components/board_skins/gallery';

    /**
     * @var array
     */
    protected static $thumbSkins = [];

    /**
     * render
     *
     * @return \Illuminate\Contracts\Support\Renderable|string
     */
    public function render()
    {
        $this->registerGetOrdersIntercept();

        if (isset($this->data['paginate'])) {
            static::attachThumbnail($this->data['paginate']);
        }

        return parent::render();
    }

    /**
     * register board handler intercept
     * intercept BoardHandler getOrder(), getsNotice()
     *
     * @return void
     */
    protected function registerGetOrdersIntercept()
    {
        intercept(
            sprintf('%s@getOrders', BoardHandler::class),
            static::class.'-board-getOrders',
            function ($func) {
                $orders = $func();
                $orders[] = ['value' => 'exceptNotice', 'text' => 'board::exceptNotice'];
                return $orders;
            }
        );

        intercept(
            sprintf('%s@getsNotice', BoardHandler::class),
            static::class.'-board-getsNotice',
            function ($func, ConfigEntity $config, $userId) {
                $notice = $func($config, $userId);

                // 공지 제외하고 보기 옵션 처리
                if (Input::get('orderType') == 'exceptNotice') {
                    return [];
                }

                foreach ($notice as $item) {
                    $thumbItem = BoardGalleryThumb::find($item->id);
                    if ($thumbItem !== null) {
                        $item->boardThumbnailFileId = $thumbItem->boardThumbnailFileId;
                        $item->boardThumbnailExternalPath = $thumbItem->boardThumbnailExternalPath;
                        $item->boardThumbnailPath = $thumbItem->boardThumbnailPath;
                    }
                }

                static::attachThumbnail($notice);
                return $notice;
            }
        );
    }

    /**
     * set using thumbnail skin id
     *
     * @param string $skinId skin id
     * @return void
     */
    public static function addThumbSkin($skinId)
    {
        static::$thumbSkins[] = $skinId;
    }

    /**
     * get thumbnail skin ids
     *
     * @return array
     */
    public static function getThumbSkins()
    {
        return static::$thumbSkins;
    }

    /**
     * skin 설정할 때 thumbnail table 을 join 할 수 있도록 intercept 등록
     *
     * @return void
     */
    protected static function interceptSetSkinTargetId()
    {
        intercept(
            sprintf('%s@setSkinTargetId', Presenter::class),
            'board_gallery_skin::set_skin_target_id',
            function ($func, $skinTargetId) {
                $func($skinTargetId);

                $request = app('request');
                $instanceConfig = InstanceConfig::instance();

                if ($request instanceof Request) {
                    $isMobile = $request->isMobile();
                } else {
                    $isMobile = false;
                }
                $assignedSkin = XeSkin::getAssigned(
                    [$skinTargetId, $instanceConfig->getInstanceId()],
                    $isMobile ? 'mobile' : 'desktop'
                );

                // target 의 스킨이 현재 skin 의 아이디와 일치하는지 확인
                if (in_array($assignedSkin->getId(), static::getThumbSkins())) {
                    // 리스트 출력할 때 gallery thumbnail 확인을 위한 table join 이벤트 등록
                    Event::listen('xe.plugin.board.articles', function ($query) {
                        $query->leftJoin(
                            'board_gallery_thumbs',
                            sprintf('%s.%s', $query->getQuery()->from, 'id'),
                            '=',
                            sprintf('%s.%s', 'board_gallery_thumbs', 'targetId')
                        );
                    });
                }
            }
        );
    }

    /**
     * attach thumbnail for list
     *
     * @param array $list list of board model
     * @return void
     */
    public static function attachThumbnail($list)
    {
        foreach ($list as $item) {
            static::bindGalleryThumb($item);
        }
    }

    /**
     * bind gallery thumbnail
     *
     * @param Board $item board model
     * @return  void
     */
    protected static function bindGalleryThumb(Board $item)
    {
        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = App::make('xe.media');

        // board gallery thumbnails 에 항목이 없는 경우
        dump($item);
        if ($item->boardThumbnailFileId === null && $item->boardThumbnailPath === null) {
            // find file by document id
            $files = File::getByFileable($item->id);
            $fileId = '';
            $externalPath = '';
            $thumbnailPath = '';

            if (count($files) == 0) {
                // find file by contents link or path
                $externalPath = static::getImagePathFromContent($item->content);

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
    protected static function getImagePathFromContent($content)
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
}
