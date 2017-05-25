<?php
/**
 * This file is board helper functions
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
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Menu\Models\MenuItem;

if (function_exists('get_board_title') === false) {
    /**
     * get board configure title or menu item title
     *
     * @param Board $board Board model instance
     * @return null|string
     */
    function get_board_title(Board $board)
    {
        /** @var Xpressengine\Plugins\Board\ConfigHandler $handler */
        $configHandler = app('xe.board.config');
        $config = $configHandler->get($board->getInstanceId());

        if ($config === null) {
            return null;
        }

        $title = '';
        if ($config->get('boardName') != null) {
            $title = xe_trans($config->get('boardName'));
        }

        if ($title != '') {
            return $title;
        }

        $menuItem = MenuItem::find($board->getInstanceId());

        if ($menuItem) {
            return xe_trans($menuItem->title);
        }

        return null;
    }
}
