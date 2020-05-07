<?php
/**
 * NewSelectUIObject
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2020 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board\Components\UIObjects\NewSelect;

use XeFrontend;
use View;
use Xpressengine\UIObject\AbstractUIObject;

/**
 * NewSelectUIObject
 *
 * DIV 방식 select
 *
 * ## 사용법
 *
 * ```php
 * uio('uiobject/board@new_select', [
 *      'name' => 'selectNameAttribute',
 *      'label' => 'label',
 *      'value' => 'value',
 *      'items' => [
 *          ['value' => 'value1', 'text' => 'text1'],
 *          ['value' => 'value2', 'text' => 'text2'],
 *      ],
 *      'open_target' => 'Dropdown을 클릭 했을 때 open class가 추가 될 selector' ex) '.target'
 * ]);
 * ```
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2020 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class NewSelectUIObject extends AbstractUIObject
{
    /**
     * @var bool
     */
    protected static $loaded = false;

    /**
     * @var string
     */
    protected static $id = 'uiobject/board@new_select';

    /**
     * render
     *
     * @return mixed
     * @throws \Exception
     */
    public function render()
    {
        XeFrontend::css('plugins/board/components/UIObjects/NewSelect/assets/css/newSelectStyle.css')->load();
        
        $args = $this->arguments;

        if (empty($args['name'])) {
            throw new \Exception;
        }
        if (empty($args['items'])) {
            $args['items'] = [];
        }
        if (empty($args['label'])) {
            $args['label'] = xe_trans('xe::select');
        }

        if (empty($args['default'])) {
            $args['default'] = '';
        }

        if (empty($args['open_target'])) {
            $args['open_target'] = '.xe-dropdown--menu--' . $args['name'];
        }
        
        if (!isset($args['value']) || $args['value'] === '') {
            $args['value'] = '';
            $args['text'] = '';
        } else {
            $selectedItem = self::getSelectedItem($args['items'], $args['value']);
            if ($selectedItem) {
                $args['text'] = $selectedItem['text'];
            } else {
                $args['value'] = '';
                $args['text'] = '';
            }
        }

        $args['scriptInit'] = false;
        if (self::$loaded === false) {
            self::$loaded = true;

            $args['scriptInit'] = true;
            
            XeFrontend::js('plugins/board/components/UIObjects/NewSelect/assets/js/newSelect.js')->load();
        }

        return View::make('board::components/UIObjects/NewSelect/newSelect', $args)->render();
    }

    private static function getSelectedItem($items, $selectedValue)
    {
        foreach($items as $item) {
            if ((string)$item['value'] === (string)$selectedValue) {
                return [
                    'value' => $item['value'],
                    'text' => $item['text']
                ];
            }

            if (self::hasChildren($item)) {
                $selectedItem = self::getSelectedItem(self::getChildren($item), $selectedValue);
                if ($selectedItem) {
                    return $selectedItem;
                }
            }
        }

        return false;
    }

    /**
     * @param array $item
     * @return boolean
     */
    public static function hasChildren($item)
    {
        return array_has($item, 'children');
    }

    /**
     * @param array $item
     * @return array
     */
    public static function getChildren($item)
    {
        if (array_has($item, 'children')) {
            return array_get($item, 'children');
        }

        return [];
    }

    public static function renderList($items, $value = null)
    {
        $args = [
            'items' => $items,
            'selectedItemValue' => $value
        ];

        return View::make('board::components/UIObjects/NewSelect/newSelectItem', $args)->render();
    }
}
