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

    /**
     * path delimiter
     *
     * @var string
     */
    protected $delimiter = '.';

    public function name()
    {
        return 'Category Design select skin';
    }

    /**
     * get view path
     *
     * @return string
     */
    public function getPath()
    {
        return 'board::views.dynamicField.category.designSelect';
    }

    public function create(array $args)
    {
        $selectItems = [];
        $categoryItems = Category::find($this->config->get('categoryId'))->items;
        foreach ($categoryItems as $categoryItem) {
            $selectItems[] = [
                'value' => $categoryItem->id,
                'text' => $categoryItem->word,
            ];
        }

        $this->addMergeData(['selectItems' => $selectItems]);

          return parent::create($args);
    }

    public function edit(array $args)
    {
        $selectItems = [];
        $categoryItems = Category::find($this->config->get('categoryId'))->items;
        foreach ($categoryItems as $categoryItem) {
            $selectItems[] = [
                'value' => $categoryItem->id,
                'text' => $categoryItem->word,
            ];
        }

        $this->addMergeData(['selectItems' => $selectItems]);

        return parent::edit($args);
    }

    public function show(array $args)
    {
        $this->path = parent::getPath();
        return parent::show($args);
    }

    public function search(array $args)
    {
        $selectItems = [];
        $categoryItems = Category::find($this->config->get('categoryId'))->items;
        foreach ($categoryItems as $categoryItem) {
            $selectItems[] = [
                'value' => $categoryItem->id,
                'text' => $categoryItem->word,
            ];
        }

        $this->addMergeData(['selectItems' => $selectItems]);

        return parent::search($args);
    }

    /**
     * Dynamic Field 설정 페이지에서 skin 설정 등록 페이지 반환
     * return html tag string
     *
     * @param ConfigEntity $config dynamic field config entity
     * @return string
     */
    public function settings(ConfigEntity $config = null)
    {
        $viewFactory = $this->handler->getViewFactory();
        return $viewFactory->make(parent::getViewPath('settings', parent::getPath()), [
            'config' => $config,
        ])->render();
    }

//
//    public function create(array $args)
//    {
//        $config = $this->config;
//
//        $categories = [];
//        $categoryItems = Category::find($this->config->get('categoryId'))->items;
//        foreach ($categoryItems as $categoryItem) {
//            $categories[] = [
//                'value' => $categoryItem->id,
//                'text' => $categoryItem->word,
//            ];
//        }
//
//        return View::make($this->getViewPath('create'), [
//            'config' => $config,
//            'categories' => $categories,
//        ])->render();
//    }
//
//    public function edit(array $args)
//    {
//        $config = $this->config;
//
//        $categories = [];
//        $categoryItems = Category::find($this->config->get('categoryId'))->items;
//        foreach ($categoryItems as $categoryItem) {
//            $categories[] = [
//                'value' => $categoryItem->id,
//                'text' => $categoryItem->word,
//            ];
//        }
//
//        $itemId = '';
//        $item = null;
//
//        if (isset($args[$config->get('id') . 'ItemId'])) {
//            $itemId = $args[$config->get('id') . 'ItemId'];
//            $item = CategoryItem::find($itemId);
//        }
//
//        return View::make($this->getViewPath('edit'), [
//            'config' => $config,
//            'categories' => $categories,
//            'itemId' => $itemId,
//            'item' => $item,
//        ])->render();
//    }
//
//    public function search(array $inputs)
//    {
//        $config = $this->config;
//        if ($config->get('searchable') !== true) {
//            return '';
//        }
//
//        $categories = [];
//        $categoryItems = Category::find($this->config->get('categoryId'))->items;
//        foreach ($categoryItems as $categoryItem) {
//            $categories[] = [
//                'value' => $categoryItem->id,
//                'text' => $categoryItem->word,
//            ];
//        }
//
//        $key = $config->get('id').'ItemId';
//
//        $itemId = '';
//        $item = '';
//        if (isset($inputs[$key])) {
//            $itemId = $inputs[$key];
//            $item = CategoryItem::find($itemId);
//        }
//
//        return View::make($this->getViewPath('search'), [
//            'config' => $config,
//            'categories' => $categories,
//            'itemId' => $itemId,
//            'item' => $item,
//        ])->render();
//    }
//
//    public function show(array $args)
//    {
//        $this->path = parent::getPath();
//
//        return parent::show($args);
//    }
//
//    public function settings(ConfigEntity $config = null)
//    {
//        $this->path = parent::getPath();
//
//        return parent::settings($config);
//    }
}
