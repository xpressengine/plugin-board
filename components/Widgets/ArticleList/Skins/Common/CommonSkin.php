<?php
namespace Xpressengine\Plugins\Board\Components\Widgets\ArticleList\Skins\Common;

use Xpressengine\Skin\GenericSkin;

class CommonSkin extends GenericSkin
{
    /**
     * @var string
     */
    protected static $path = 'board::components/Widgets/ArticleList/Skins/Common/views';

    /**
     * @var string
     */
    protected static $viewDir = '';

    protected static $info = [
        'support' => [
            'mobile' => true,
            'desktop' => true
        ]
    ];
}
