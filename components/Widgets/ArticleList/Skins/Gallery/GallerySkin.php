<?php
namespace Xpressengine\Plugins\Board\Components\Widgets\ArticleList\Skins\Gallery;

use Xpressengine\Media\Models\Image;
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
                $item->boardThumbnailFileId = $thumbItem->boardThumbnailFileId;
                $item->boardThumbnailExternalPath = $thumbItem->boardThumbnailExternalPath;
                $item->boardThumbnailPath = $thumbItem->boardThumbnailPath;
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
        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = app('xe.media');

        // board gallery thumbnails 에 항목이 없는 경우
        if ($item->boardThumbnailFileId === null && $item->boardThumbnailPath === null) {
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
     * 위젯 설정 페이지에 출력할 폼을 출력한다.
     *
     * @param array $args 설정값
     *
     * @return string
     */
    public function renderSetting(array $args = [])
    {
        return $view = View::make(sprintf('%s/views/setting', static::$path), [
        ]);
    }
}
