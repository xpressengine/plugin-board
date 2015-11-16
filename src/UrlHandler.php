<?php
/**
 * Board url handler
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board;

use Xpressengine\Document\DocumentHandler;
use Xpressengine\Config\ConfigEntity;

/**
 * Url handler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
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
     * @param SlugRepository $slug   document handler
     * @param ConfigEntity   $config config entity
     */
    public function __construct(SlugRepository $slug, ConfigEntity $config = null)
    {
        $this->slug = $slug;
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
     * @param ItemEntity $item   board item entity
     * @param array      $params parameters
     * @return string
     */
    public function getShow(ItemEntity $item, $params = [])
    {
        if ($item->getSlug() !== null) {
            return $this->getSlug($item->getSlug()->slug, $params);
        }

        $id = $item->id;
        if (($shortId = $item->getShortId()) !== null) {
            $id = $shortId->getId();
        }
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
        $slug = $this->slug->findById($id, $this->config->get('boardId'));

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

        return $this->get('slug', $params);
    }

    /**
     * get slug string
     *
     * @param SlugRepository $slugRepository slug repository
     * @param string         $slug           slug
     * @param string         $id             document id
     * @param string         $instanceId     board instance id
     * @return string
     */
    public function makeSlug(SlugRepository $slugRepository, $slug, $id, $instanceId)
    {
        $slugInfo = $slugRepository->find($slug, $instanceId);

        if ($slugInfo === null) {
            return $slug;
        }

        if ($slugInfo['id'] == $id) {
            return $slug;
        }

        if ($slugInfo['instanceId'] != $instanceId) {
            return $slug;
        }

        // slug 에 문자열 추가
        $slug = $slugRepository->incrementName($slug, $instanceId);

        return $this->makeSlug($slugRepository, $slug, $id, $instanceId);
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
