<?php
/**
 * Handler
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
namespace Xpressengine\Plugins\Board\Commands;

use App\Console\Commands\SkinMake;
use ReflectionClass;
use Xpressengine\Plugin\PluginEntity;

/**
 * BaordSkinMake
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class BoardSkinMake extends SkinMake
{
    protected $signature = 'make:board_skin
                        {plugin_dir : destination plugin directory name}
                        {skin_dir : skin name, make directory name}
                        {--path= : The path of skin directory. If first segment is same, it will be ignored. default path is components/Skins/Board/skin_name}
                        {--id= : The path of skin class file}
                        {--title= : The title of the skin, default skin id}
                        {--description= : The description of the skin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new board skin';

    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function fire()
    {
        // get plugin info
        $plugin = $this->getPlugin();
        $pluginClass = new ReflectionClass($plugin->getClass());
        $namespace = $pluginClass->getNamespaceName();

        // get skin info
        $pluginDir = $this->getPluginDir();
        $skinDir = $this->getSkinDir();
        $path = $this->getPath();
        $skinClass = studly_case(basename($path)).'Skin';
        $skinFile = $this->getSkinFile($plugin, $path, $skinClass); // path_to_skin_dir/Skin.php
        $skinTarget = $this->getSkinTarget();
        $skinId = $this->getSkinId($plugin, $skinClass, $skinTarget); // myplugin@skin
        $skinTitle = $this->getSkinTitle();
        $description = $this->getSkinDescription($skinId, $plugin);
        $skinNamespace = sprintf(
            '%s\\%s',
            $this->getSkinNamespaceName($namespace),
            ucwords(camel_case($skinDir))
        );

        $this->attr = compact(
            'plugin',
            'path',
            'pluginClass',
            'namespace',
            'skinClass',
            'skinTarget',
            'skinFile',
            'skinId',
            'skinTitle',
            'description',
            'skinNamespace'
        );

        // print and confirm the information of skin
        if ($this->confirmInfo() === false) {
            return false;
        }

        try {
            // make directories and files
            $this->copySkinDirectory();
            $this->makeSkinClass();

            // composer.json 파일 수정
            if ($this->registerSkin() === false) {
                throw new \Exception('Writing to composer.json file was failed.');
            }

            $this->runComposerDump($plugin->getPath());
        } catch (\Exception $e) {
            $this->clean();
            throw $e;
        }

        $this->info("Skin is created successfully.");
    }

    /**
     * replaceCode
     *
     * @param $stub
     * @param $search
     * @param $replace
     *
     * @return $this
     */
    protected function replaceCode(&$stub, $search, $replace)
    {
        $stub = str_replace($search, $replace, $stub);
        return $this;
    }

    /**
     * Get the stub file for the generator.
     *
     * @param string $filename filename
     * @return string
     */
    protected function getStub($filename)
    {
        return __DIR__.'/stubs/'.$filename;
    }

    /**
     * get plugin dir
     *
     * @return string
     */
    protected function getPluginDir()
    {
        return $this->argument('plugin_dir');
    }

    /**
     * get skin dir
     *
     * @return string
     */
    protected function getSkinDir()
    {
        return $this->argument('skin_dir');
    }

    /**
     * get Plugin
     *
     * @return PluginEntity
     * @throws \Exception
     */
    protected function getPlugin()
    {
        $plugin = $this->getPluginDir();
        $plugin = app('xe.plugin')->getPlugin($plugin);
        if ($plugin === null) {
            throw new \Exception("Unable to find a plugin to locate the skin file. plugin[$plugin] is not found.");
        }

        return $plugin;
    }

    /**
     * get skin directory
     *
     * @return string
     */
    protected function getPath()
    {
        $path = $this->option('path');
        $skinDir = $this->getSkinDir();

        if ($path === false || $path === null) {
            $path = sprintf(
                'components/Skins/Board/%s',
                ucwords(camel_case($skinDir))
            );
        } else {
            $parts = explode('/', $path);
            if ($parts[0] == '') {
                array_shift($parts);
            }
            if ($parts[0] == 'plugins') {
                array_shift($parts);
            }
            if ($parts[0] == $this->getPluginDir()) {
                array_shift($parts);
            }

            $last = array_pop($parts);
            if ($last == '') {
                array_push($parts, $skinDir);
            } else {
                array_push($parts, $last);
                array_push($parts, $skinDir);
            }

            $path = implode('/', $parts);
        }

        return $path;
    }

    /**
     * getSkinTitle
     *
     * @return array|string
     */
    protected function getSkinTitle()
    {
        $title = $this->option('title');
        if ($title === false || $title === null) {
            $title = sprintf('%s Board skin', $this->getSkinDir());
        }
        return $title;
    }

    /**
     * get skin target
     *
     * @return string
     */
    protected function getSkinTarget()
    {
        return 'module/board@board';
    }

    /**
     * getSkinFile
     *
     * @param PluginEntity $plugin
     * @param              $path
     * @param              $skinClass
     *
     * @return array|string
     * @throws \Exception
     */
    protected function getSkinFile(PluginEntity $plugin, $path, $skinClass)
    {
        $path = $path."/$skinClass.php";
        if (file_exists($plugin->getPath($path))) {
            throw new \Exception("file[$path] already exists.");
        }
        return $path;
    }

    /**
     * @param $namespace
     * @return string
     */
    protected function getSkinNamespaceName($namespace)
    {
        return sprintf('%s\\Components\\Skins\\Board', $namespace);
    }

    /**
     * attr
     *
     * @param $key
     *
     * @return mixed
     */
    private function attr($key)
    {
        return array_get($this->attr, $key);
    }

    /**
     * makeSkinClass
     *
     * @throws \Exception
     */
    protected function makeSkinClass()
    {
        $plugin = $this->attr('plugin');
        $skinFile = $this->attr('skinFile');
        $path = $plugin->getPath($skinFile);

        $code = $this->buildCode('board_skin/skin.stub');

        $this->files->put($path, $code);
    }

    protected function copySkinDirectory()
    {
        $plugin = $this->attr('plugin');
        $path = $plugin->getPath($this->attr('path'));

        if (!$this->files->copyDirectory(__DIR__.'/stubs/board_skin', $path)) {
            throw new \Exception("Unable to create skin directory[$path]. please check permission.");
        }
        rename($path.'/info.stub', $path.'/info.php');

        $viewFileNames = [
            'create', 'edit', 'guestId', 'index', 'preview', 'revision', 'setting',
            'show', 'votedModal', 'votedUserList', 'votedUsers',
        ];

        $replaceSearch = 'plugins/';
        $replacePath = substr($path, strpos($path, $replaceSearch) + strlen($replaceSearch));
        foreach ($viewFileNames as $fileName) {
            $stub = sprintf('%s/views/%s.blade.stub', $path, $fileName);
            if (file_exists($stub)) {
                $code = $this->files->get($stub);
                $this->replaceCode($code, 'DummyPath', $replacePath);
                $this->files->put($stub, $code);

                $rename = sprintf('%s/views/%s.blade.php', $path, $fileName);
                rename($stub, $rename);
            }

        }
        unlink($path.'/skin.stub');
    }
}
