<?php
namespace Xpressengine\Plugins\Board\Widgets\Skins;

use Xpressengine\Skin\GenericSkin;

class DefaultSkin extends GenericSkin
{
    /**
     * @var string
     */
    protected static $path = 'board.views.widgets.list.skins.default';

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
