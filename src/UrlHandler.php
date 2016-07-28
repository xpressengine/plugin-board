<?php
/**
 * UrlHandler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Board;

use Xpressengine\Document\DocumentHandler;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Models\Slug;

/**
 * UrlHandler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 */
class UrlHandler
{

    /**
     * @var DocumentHandler
     */
    protected $document;

    /**
     * @var ConfigEntity
     */
    protected $config;

    /**
     * create instance
     *
     * @param ConfigEntity $config config entity
     */
    public function __construct(ConfigEntity $config = null)
    {
        $this->config = $config;
    }

    /**
     * set Config
     *
     * @param ConfigEntity $config board config entity
     * @return void
     */
    public function setConfig(ConfigEntity $config)
    {
        $this->config = $config;
    }

    /**
     * get user controller's url
     *
     * @param string       $name   get url name
     * @param array        $params parameters
     * @param ConfigEntity $config null or board config entity
     * @return string
     */
    public function get($name = 'index', $params = [], ConfigEntity $config = null)
    {
        if ($this->config == null && $config == null) {
            throw new Exceptions\InvalidConfigException;
        }

        if ($config == null) {
            $config = $this->config;
        }

        return instanceRoute($name, $params, $config->get('boardId'));
    }

    /**
     * get show page url
     * 1. slug 가 있다면 slug 로 url return
     * 1. short id 가 있다면 short id 로 url return
     * 1. 기본 id 로 url return
     *
     * @param Board $board  board item entity
     * @param array $params parameters
     * @return string
     */
    public function getShow(Board $board, $params = [])
    {
        $slug = $board->slug;
        if ($slug != null) {
            return $this->getSlug($slug->slug, $params);
        }

        $id = $board->id;
        $params['id'] = $id;
        return $this->get('show', $params);
    }

    /**
     * get slug url by document id
     * document 에서 지원하는 slug 주소 반환
     *
     * @param string $id document id
     * @return string
     */
    public function getSlugById($id)
    {
        $slug = Slug::where(id, $id)->where('instanceId', $this->config->get('boardId'));

        if ($slug === null) {
            return '';
        }

        return $this->getSlug($slug->slug);
    }

    /**
     * get slug url
     *
     * @param string $slug   slug
     * @param array  $params parameters
     * @return string
     */
    public function getSlug($slug, array $params = [])
    {
        unset($params['id']);
        $params['slug'] = $slug;

        // 페이지 정보는 넘기지 않음
        unset($params['page']);

        return route('archives', $params);
    }

    /**
     * get url query string to array
     *
     * @param string $queryString url query string
     * @return array
     */
    public function queryStringToArray($queryString)
    {
        parse_str($queryString, $array);
        return $array;
    }

    /**
     * get manage url
     *
     * @param string $name   get url name
     * @param array  $params parameters
     * @return string
     */
    public function managerUrl($name, $params = [])
    {
        return route('manage.board.board.' . $name, $params);
    }
}
