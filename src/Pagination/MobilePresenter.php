<?php
/**
 * MobilePresenter
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
namespace Xpressengine\Plugins\Board\Pagination;

use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Pagination\Presenter as PresenterContract;
use Illuminate\Pagination\UrlWindowPresenterTrait;
use Illuminate\Pagination\UrlWindow;

/**
 * MobilePresenter
 *
 * mobile pagination 디자인 교체
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class MobilePresenter implements PresenterContract
{
    use UrlWindowPresenterTrait;

    /**
     * The paginator implementation.
     *
     * @var \Illuminate\Contracts\Pagination\Paginator
     */
    protected $paginator;

    /**
     * The URL window data structure.
     *
     * @var array
     */
    protected $window;

    /**
     * Create a new Bootstrap presenter instance.
     *
     * @param  PaginatorContract $paginator paginator
     * @param  UrlWindow|null    $window    window
     */
    public function __construct(PaginatorContract $paginator, UrlWindow $window = null)
    {
        $this->paginator = $paginator;
        $this->window = is_null($window) ? UrlWindow::make($paginator) : $window->get();
    }

    /**
     * Determine if the underlying paginator being presented has pages to show.
     *
     * @return bool
     */
    public function hasPages()
    {
        return $this->paginator->hasPages();
    }

    /**
     * Convert the URL window into Bootstrap HTML.
     *
     * @return string
     */
    public function render()
    {
        if ($this->hasPages()) {
            return sprintf(
                '<div class="bd_paginate v2 xe-visible-xs">%s %s %s</div>',
                $this->getPreviousButton(),
                $this->getCurrentPosition(),
                $this->getNextButton()
            );
        }

        return '';
    }

    /**
     * Get the previous page pagination element.
     *
     * @param string $text text
     * @return string
     */
    public function getPreviousButton($text = '&laquo;')
    {
        // If the current page is less than or equal to one, it means we can't go any
        // further back in the pages, so we will render a disabled previous button
        // when that is the case. Otherwise, we will give it an active "status".
        if ($this->paginator->currentPage() <= 1) {
            return '<span class="btn_pg btn_prev"><i class="xi-angle-left">'.
            '<span class="bd_hidden">'.$text.'</span></i></span>';
        }

        $url = $this->paginator->url(
            $this->paginator->currentPage() - 1
        );

        return '<a href="'.$url.'" class="btn_pg btn_prev"><i class="xi-angle-left">'.
        '<span class="bd_hidden">'.$text.'</span></i></a>';
    }

    /**
     * Get the next page pagination element.
     *
     * @param string $text text
     * @return string
     */
    public function getNextButton($text = '&raquo;')
    {
        // If the current page is greater than or equal to the last page, it means we
        // can't go any further into the pages, as we're already on this last page
        // that is available, so we will make it the "next" link style disabled.
        if (! $this->paginator->hasMorePages()) {
            return '<span class="btn_pg btn_next"><i class="xi-angle-right"><span class="bd_hidden">'
            .$text.'</span></i></span>';
        }

        $url = $this->paginator->url($this->paginator->currentPage() + 1);

        return '<a href="'.$url.'" class="btn_pg btn_next"><i class="xi-angle-right">'.
        '<span class="bd_hidden">'.$text.'</span></i></a>';
    }

    /**
     * get current position
     *
     * @return string
     */
    public function getCurrentPosition()
    {
        return '<span class="pg_box"><strong>'.$this->currentPage().
        '</strong> / <span>'.$this->lastPage().'</span></span>';
    }

    /**
     * Get the current page from the paginator.
     *
     * @return int
     */
    protected function currentPage()
    {
        return $this->paginator->currentPage();
    }

    /**
     * Get the last page from the paginator.
     *
     * @return int
     */
    protected function lastPage()
    {
        return $this->paginator->lastPage();
    }
}
