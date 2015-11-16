<?php
namespace Xpressengine\Plugins\Board\FieldSkins\Category;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\FieldSkins\Category\DefaultSkin;
use Category;
use View;

class DesignSelectSkin extends DefaultSkin
{
    //
    /**
     * name 규치에 맞지 않지만.. 이 이름으로 register 를 변경함
     * Category 기본 게시판 스킨 변경
     * BoardSkin 이 render 할 때 적용됨
     *
     * @var string
     * @see BoardSkin::render()
     */
    protected static $id = 'FieldType/xpressengine@Category/FieldSkin/xpressengine@default';

    protected $name = 'Category Design select skin';
    protected $description = '공식사이트 홈페이지에서 Category DynamicField Type 위해 사용하는 스킨';

    /**
     * get view path
     *
     * @param string $name view name
     * @return string
     */
    protected function getPath($name)
    {
        return 'board::views.dynamicField.category.designSelect.'.$name;
    }

    public function settings(ConfigEntity $config = null)
    {
        return parent::search($config, $this->getPath('createSkin'));
    }

    public function create(array $inputs)
    {
        return parent::create($inputs, $this->getPath('create'));
    }

    public function edit(array $args)
    {
        return parent::edit($args, $this->getPath('edit'));
    }

    /**
     * @param array $inputs
     */
    public function search(array $inputs)
    {
        return parent::search($inputs, $this->getPath('search'));
    }
}
