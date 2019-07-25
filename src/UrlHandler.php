<?php
/**
 * UrlHandler
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
namespace Xpressengine\Plugins\Board;

use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Board\Models\BoardSlug;

/**
 * UrlHandler
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class UrlHandler
{
    /**
     * @var string
     */
    protected $instanceId;

    /**
     * @var ConfigEntity
     */
    protected $config;

    /**
     * set instance id
     *
     * @param string $instanceId instance id
     * @return $this
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;
        return $this;
    }

    /**
     * set Config
     *
     * @param ConfigEntity $config board config entity
     * @return $this
     */
    public function setConfig(ConfigEntity $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * get user controller's url
     *
     * @param string $name       get url name
     * @param array  $params     parameters
     * @param string $instanceId board instance id
     * @return string
     */
    public function get($name = 'index', array $params = [], $instanceId = null)
    {
        if ($instanceId == null) {
            $instanceId = $this->instanceId;
        }
        return instance_route($name, $params, $instanceId);
    }

    /**
     * get show page url
     * 1. slug 가 있다면 slug 로 url return
     * 1. short id 가 있다면 short id 로 url return
     * 1. 기본 id 로 url return
     *
     * @param Board        $board  board item entity
     * @param array        $params parameters
     * @param ConfigEntity $config board config
     * @return string
     */
    public function getShow(Board $board, $params = [], ConfigEntity $config = null)
    {
        if ($config === null) {
            $config = $this->config;
        }

        if ($config !== null && $config->get('urlType') == 'slug') {
            $slug = $board->slug;
            if ($slug != null) {
                return $this->getSlug($slug->slug, $params, $slug->instance_id);
            }
        }

        $id = $board->id;
        $params['id'] = $id;
        return $this->get('show', $params, $board->instance_id);
    }

    /**
     * get slug url
     *
     * @param string $slug       slug
     * @param array  $params     parameters
     * @param string $instanceId board instance id
     * @return string
     */
    public function getSlug($slug, array $params, $instanceId)
    {
        unset($params['id']);
        $params['slug'] = $slug;

        // 페이지 정보는 넘기지 않음
        unset($params['page']);

        return $this->get('slug', $params, $instanceId);
    }

    /**
     * get archives url
     *
     * @param string $slug   slug
     * @param array  $params parameters
     * @return string
     */
    public function getArchives($slug, array $params = [])
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
        return route('settings.board.board.' . $name, $params);
    }
}
