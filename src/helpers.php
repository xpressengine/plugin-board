<?php
/**
 * This file is board helper functions
 *
 * PHP version 7
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Support\Notifications\Notice;
use Illuminate\Notifications\Notifiable;

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

if (function_exists('send_notice_email') === false) {
    /**
     * send notice mail
     *
     * @param string        $tag             tag
     * @param string        $email           email
     * @param string        $title           title
     * @param string        $contents        contents
     * @param callable|null $subjectResolver subject resolver
     *
     * @return void
     */
    function send_notice_email($tag, $email, $title, $contents, callable $subjectResolver = null)
    {
        (new class($tag, $email, $title, $contents, $subjectResolver) {
            use Notifiable;

            protected $tag;
            protected $email;
            protected $title;
            protected $contents;
            protected $subjectResolver;

            /**
             *  constructor.
             *
             * @param string        $tag             tag
             * @param string        $email           email
             * @param string        $title           title
             * @param string        $contents        contents
             * @param callable|null $subjectResolver subject resolver
             */
            public function __construct($tag, $email, $title, $contents, callable $subjectResolver = null)
            {
                $this->tag = $tag;
                $this->email = $email;
                $this->title = $title;
                $this->contents = $contents;
                $this->subjectResolver = $subjectResolver;
            }

            /**
             * Invoke the instance
             *
             * @return void
             */
            public function __invoke()
            {
                if ($this->subjectResolver != null) {
                    Notice::setSubjectResolver($this->subjectResolver);
                }

                $this->notify(new Notice($this->email, $this->title, $this->contents));

                Notice::setSubjectResolverToNull();
            }

            /**
             * Get the notification routing information for the given driver.
             *
             * @param string $driver driver
             * @return mixed
             */
            public function routeNotificationFor($driver)
            {
                return $this->email;
            }
        })();
    }
}
