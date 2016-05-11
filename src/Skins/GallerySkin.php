<?php
/**
 * GallerySkin
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
namespace Xpressengine\Plugins\Board\Skins;

use App\Facades\XeFrontend;
use Illuminate\Database\Eloquent\Relations\Relation;
use Xpressengine\Http\Request;
use Xpressengine\Media\Models\Image;
use Xpressengine\Plugins\Board\Models\BoardGalleryThumb;
use Xpressengine\Plugins\Board\Skins\DynamicField\DesignSelectSkin;
use Xpressengine\Plugins\Board\Skins\PaginationMobilePresenter;
use Xpressengine\Plugins\Board\Skins\PaginationPresenter;
use Xpressengine\Presenter\Presenter;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\Skin\AbstractSkin;
use XeSkin;
use View;
use Event;
use Xpressengine\Storage\File;
use Xpressengine\Plugins\Board\Modules\Board as BoardModule;

/**
 * GallerySkin
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class GallerySkin extends DefaultSkin
{
    protected static $skinAlias = 'board::views.gallerySkin';

    static protected $thumbSkins = [];

    /**
     * render
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // call customizer
        // view 아이디를 기준으로 Customizer 호출
        $customizer = $this->view . 'Customizer';
        if (method_exists($this, $customizer)) {
            $this->$customizer();
        }

        $this->data['skinAlias'] = static::$skinAlias;

        // 리스팅을 제외한 모든 디자인은 기본 스킨의 디자인 사용
        $view = View::make('board::views.defaultSkin._frame', $this->data);
        if ($this->view === 'index') {

            static::attachThumbnail($this->data['paginate']);

            $view->content = View::make(
                sprintf('%s.%s', static::$skinAlias, $this->view),
                $this->data
            )->render();
        } else {

            if ($this->view === 'show') {
                static::attachThumbnail($this->data['paginate']);
            }

            $view->content = View::make(
                sprintf('%s.%s', parent::$skinAlias, $this->view),
                $this->data
            )->render();
        }

        return $view;
    }

    /**
     * get manage URI
     *
     * @return string
     */
    public static function getSettingsURI()
    {
    }

    /**
     * Register 에 등록될 때
     *
     * @return void
     */
    public static function boot()
    {
        static::addThumbSkin(static::getId());
        static::interceptSetSkinTargetId();
    }

    /**
     * set using thumbnail skin id
     *
     * @param $skinId
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
                    Event::listen('xe.plugin.board.list', function ($query) {
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
        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = \App::make('xe.media');

        foreach ($list as $item) {
            // board gallery thumbnails 에 항목이 없는 경우
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
                        // 어떤 크기의 썸네일을 사용할 것인지 스킨 설정을 통해 결정(두배 이미지가 좋다함)
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
