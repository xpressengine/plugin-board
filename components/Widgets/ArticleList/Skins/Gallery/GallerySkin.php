<?php

namespace Xpressengine\Plugins\Board\Components\Widgets\ArticleList\Skins\Gallery;

use Xpressengine\Media\Repositories\ImageRepository;
use Xpressengine\Plugins\Board\ConfigHandler;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Models\BoardGalleryThumb;
use Xpressengine\Plugins\Board\Components\Modules\BoardModule;
use Xpressengine\Skin\GenericSkin;
use View;

class GallerySkin extends GenericSkin
{
    /**
     * @var string
     */
    protected static $path = 'board/components/Widgets/ArticleList/Skins/Gallery';

    public function render()
    {
        $data = $this->data;
        $list = array_get($data, 'list');
        foreach ($list as $item) {
            $thumbItem = BoardGalleryThumb::find($item->id);
            if ($thumbItem !== null) {
                $item->board_thumbnail_file_id = $thumbItem->board_thumbnail_file_id;
                $item->board_thumbnail_external_path = $thumbItem->board_thumbnail_external_path;
                $item->board_thumbnail_path = $thumbItem->board_thumbnail_path;
            }
        }
        $this->attachThumbnail($list);


        return parent::render();
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
        if ($item->thumb !== null) {
            $item->board_thumbnail_file_id = $item->thumb->board_thumbnail_file_id;
            $item->board_thumbnail_external_path = $item->thumb->board_thumbnail_external_path;
            $item->board_thumbnail_path = $item->thumb->board_thumbnail_path;

            return;
        }

        $instanceId = $item->instance_id;
        $configHandler = app(ConfigHandler::class);

        $boardInstanceConfigEntity = $configHandler->get($instanceId);
        $isReplaceableCoverImage = $boardInstanceConfigEntity->get('isReplaceableCoverImage');

        if ($isReplaceableCoverImage === null || $isReplaceableCoverImage === true) {
            /** @var \Xpressengine\Media\MediaManager $mediaManager */
            $mediaManager = app('xe.media');

            // board gallery thumbnails 에 항목이 없는 경우
            // find file by document id
            $files = \XeStorage::fetchByFileable($item->id);
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

                    $imageRepository = new ImageRepository();
                    $media = $imageRepository->getThumbnail(
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

            $item->board_thumbnail_file_id = $fileId;
            $item->board_thumbnail_external_path = $externalPath;
            $item->board_thumbnail_path = $thumbnailPath;

            // 없을 경우 출력될 디폴트 이미지 (스킨의 설정으로 뺄 수 있을것 같음)
            if ($item->board_thumbnail_path == '') {
                $item->board_thumbnail_path = asset('assets/core/common/img/default_image_1200x800.jpg');
            }
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
            $path = $matches[1][0];
        }

        return $path;
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
        return $view = View::make(sprintf('%s/views/setting', static::$path), [
            'args' => $args
        ]);
    }
}
