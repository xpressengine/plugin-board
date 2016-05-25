<?php
/**
 *
 */
namespace Xpressengine\Plugins\Board\Skins;

use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Pagination\Presenter as PresenterContract;
use Illuminate\Pagination\UrlWindowPresenterTrait;
use Illuminate\Pagination\UrlWindow;

/**
 * Class Paginationresenter
 * @package Xpressengine\Plugins\Board
 */
class PaginationPresenter implements PresenterContract
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
     * @param PaginatorContract $paginator paginator
     * @param UrlWindow|null    $window    url window
     * @param int               $pageCount page link count
     */
    public function __construct(PaginatorContract $paginator, UrlWindow $window = null, $pageCount = 3)
    {
        $this->paginator = $paginator;
        $this->window = is_null($window) ? UrlWindow::make($paginator, $pageCount) : $window->get();
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
                '<div class="bd_paginate xe-hidden-xs">%s %s %s</div>',
                $this->getPreviousButton(),
                $this->getLinks(),
                $this->getNextButton()
            );
        }

        return '';
    }

    /**
     * Get the previous page pagination element.
     *
     * @param  string  $text
     * @return string
     */
    public function getPreviousButton($text = '&laquo;')
    {
        // If the current page is less than or equal to one, it means we can't go any
        // further back in the pages, so we will render a disabled previous button
        // when that is the case. Otherwise, we will give it an active "status".
        if ($this->paginator->currentPage() <= 1) {
            return '<span class="btn_pg btn_prev"><i class="xi-angle-left"><span class="bd_hidden">'.$text.'</span></i></span>';
        }

        $url = $this->paginator->url(
            $this->paginator->currentPage() - 1
        );

        return '<a href="'.$url.'" class="btn_pg btn_prev"><i class="xi-angle-left"><span class="bd_hidden">'.$text.'</span></i></a>';
    }

    /**
     * Get the next page pagination element.
     *
     * @param  string  $text
     * @return string
     */
    public function getNextButton($text = '&raquo;')
    {
        // If the current page is greater than or equal to the last page, it means we
        // can't go any further into the pages, as we're already on this last page
        // that is available, so we will make it the "next" link style disabled.
        if (! $this->paginator->hasMorePages()) {
            return '<span class="btn_pg btn_next"><i class="xi-angle-right"><span class="bd_hidden">'.$text.'</span></i></span>';
        }

        $url = $this->paginator->url($this->paginator->currentPage() + 1);

        return '<a href="'.$url.'" class="btn_pg btn_next"><i class="xi-angle-right"><span class="bd_hidden">'.$text.'</span></i></a>';
    }

    /**
     * Get HTML wrapper for an available page link.
     *
     * @param  string  $url
     * @param  int  $page
     * @param  string|null  $rel
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page, $rel = null)
    {
        $rel = is_null($rel) ? '' : ' rel="'.$rel.'"';

        return '<a href="'.htmlentities($url).'"'.$rel.'>'.$page.'</a>';
    }

    /**
     * Get HTML wrapper for disabled text.
     *
     * @param  string  $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<span>'.$text.'</span>';
    }

    /**
     * Get HTML wrapper for active text.
     *
     * @param  string  $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<strong>'.$text.'</strong>';
    }

    /**
     * Get a pagination "dot" element.
     *
     * @return string
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper('...');
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
