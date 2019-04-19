<?php
namespace Xpressengine\Plugins\Board\Components\Widgets\ArticleList\Skins\Common;

use Xpressengine\Skin\GenericSkin;
use View;

class CommonSkin extends GenericSkin
{
    /**
     * @var string
     */
    protected static $path = 'board/components/Widgets/ArticleList/Skins/Common';

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
            'args'=>$args
        ]);
    }
}
