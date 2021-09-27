<?php

declare(strict_types=1);

namespace Xpressengine\Plugins\Board\Commands;

/**
 * Class XeGalleryBoardSkinMake
 *
 * XE Gallery Board Skin Make Command
 *
 * @package Xpressengine\Plugins\Board\Commands
 */
final class XeGalleryBoardSkinMake extends BoardSkinMake
{
    use RegisterArtisan;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:xe_gallery_board_skin
                        {plugin : The plugin where the skin will be located}
                        {name : The name of skin to create}
                        {--id= : The identifier of skin. default "<plugin>@<name>"}
                        {--path= : The path of skin. Enter the path to under the plugin. ex) SomeDir/SkinDir}
                        {--class= : The class name of skin. default "<name>Skin"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new xe gallery board skin';

    /**
     * get stub path
     *
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__.'/stubs/xe_gallery_board_skin';
    }

    /**
     * get title input
     *
     * @return array|string
     */
    protected function getTitleInput()
    {
        return $this->option('title') ?: studly_case($this->getComponentName()) . ' XE Gallery Board skin';
    }
}