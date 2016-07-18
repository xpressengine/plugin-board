<?php
/**
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Skins\DynamicField;

use View;
use Xpressengine\Category\Models\Category;
use Xpressengine\Category\Models\CategoryItem;
use Xpressengine\Config\ConfigEntity;
use App\FieldSkins\Category\DefaultSkin;

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

    public function create(array $inputs)
    {
        $config = $this->config;

        $categories = [];
        $categoryItems = Category::find($this->config->get('categoryId'))->items;
        foreach ($categoryItems as $categoryItem) {
            $categories[] = [
                'value' => $categoryItem->id,
                'text' => $categoryItem->word,
            ];
        }

        return View::make($this->getPath('create'), [
            'config' => $config,
            'categories' => $categories,
        ])->render();
    }

    public function edit(array $args)
    {
        $config = $this->config;

        $categories = [];
        $categoryItems = Category::find($this->config->get('categoryId'))->items;
        foreach ($categoryItems as $categoryItem) {
            $categories[] = [
                'value' => $categoryItem->id,
                'text' => $categoryItem->word,
            ];
        }

        $itemId = '';
        $item = null;

        if (isset($args[$config->get('id') . 'ItemId'])) {
            $itemId = $args[$config->get('id') . 'ItemId'];
            $item = CategoryItem::find($itemId);
        }

        return View::make($this->getPath('edit'), [
            'config' => $config,
            'categories' => $categories,
            'itemId' => $itemId,
            'item' => $item,
        ])->render();
    }

    public function search(array $inputs)
    {
        $config = $this->config;
        if ($config->get('searchable') !== true) {
            return '';
        }

        $categories = [];
        $categoryItems = Category::find($this->config->get('categoryId'))->items;
        foreach ($categoryItems as $categoryItem) {
            $categories[] = [
                'value' => $categoryItem->id,
                'text' => $categoryItem->word,
            ];
        }

        $key = $config->get('id').'ItemId';

        $itemId = '';
        $item = '';
        if (isset($inputs[$key])) {
            $itemId = $inputs[$key];
            $item = CategoryItem::find($itemId);
        }

        return View::make($this->getPath('search'), [
            'config' => $config,
            'categories' => $categories,
            'itemId' => $itemId,
            'item' => $item,
        ])->render();
    }
}
