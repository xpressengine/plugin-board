<?php

namespace Xpressengine\Plugins\Board\Commands\ArticleList;

use Xpressengine\Plugins\Board\Commands\RegisterArtisan;

final class GallerySkinMake extends \App\Console\Commands\SkinMake
{
    use RegisterArtisan;

    /**
     * 콘솔에서 사용하는 코멘드
     *
     * @var string
     */
    protected $signature = 'make:board-gallery-article-list-widget-skin
        {plugin : The plugin where the skin will be located}
        {name : The name of skin to create}
        {--id= : The identifier of skin. default "<plugin>@<name>"}
        {--path= : The path of skin. Enter the path to under the plugin. ex) SomeDir/SkinDir}
        {--class= : The class name of skin. default "<name>Skin"}';

    /**
     * 콘솔에 노출되는 설명
     *
     * @var string
     */
    protected $description = "Create a new board gallery widget skin of XpressEngine";

    /**
     * 스킨 타겟
     *
     * @return string
     */
    protected function getSkinTarget()
    {
        return 'widget/board@list';
    }

    /**
     * 기준이 되는 코드 위치
     *
     * @return string
     */
    protected function getStubPath()
    {
        return __DIR__ . '/stubs/gallery';
    }

    /**
     * 베이스(Stub) 파일을 바탕으로 플로그인에서 사용할 파일을 생성합니다.
     *
     * @param $attr
     * @return void
     * @throws \Exception
     */
    protected function makeUsable($attr)
    {
        $plugin = $attr['plugin'];
        $path = $plugin->getPath($attr['path']);

        $this->makeSkinClass($attr);

        /** Info */
        $this->renameStubFile($path . '/info.stub');

        /** View Files */
        $viewPaths = [sprintf('%s/views', $path)];

        foreach ($viewPaths as $viewPath)
        {
            $files = $this->files->files($viewPath, false);

            foreach ($files as $file) {
                $this->renameStubFile($file->getPathname());
            }
        }
    }

    /**
     * rename Stub 파일
     *
     * @param $fileName
     */
    private function renameStubFile($fileName)
    {
        $changedFile = str_replace('stub', 'php', $fileName);
        rename($fileName, $changedFile);
    }
}
