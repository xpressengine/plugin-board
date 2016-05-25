<?php
/**
 * Board
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Models;

use Xpressengine\Counter\Models\CounterLog;
use Xpressengine\Document\Models\Document;
use Xpressengine\Plugins\Comment\CommentUsable;
use Xpressengine\Routing\InstanceRoute;
use Xpressengine\Storage\File;
use Xpressengine\User\Models\Guest;

/**
 * Board
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Board extends Document implements CommentUsable
{
    /**
     * get user id
     *
     * @return string
     */
    public function getUserId()
    {
        $userId = $this->getAttribute('userId');
        if ($this->getAttribute('userType') === self::USER_TYPE_ANONYMITY) {
            $userId = '';
        }

        return $userId;
    }

    /**
     * Return is new
     *
     * @param int $hour hour config value
     * @return bool
     */
    public function isNew($hour)
    {
        return strtotime($this->getAttribute(static::CREATED_AT)) + ($hour * 86400) > time();
    }

    /**
     * scope notice
     *
     * @param $query
     * @return $query
     */
    public function scopeNotice($query)
    {
        return $query->whereStatus(self::STATUS_NOTICE);
    }

    /**
     * get assent counter log
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assents()
    {
        return $this->hasMany(CounterLog::class, 'targetId')->where('counterName', 'vote')->where('counterOption', 'assent');
    }

    /**
     * get slug
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function boardSlug()
    {
        return $this->hasOne('Xpressengine\Plugins\Board\Models\BoardSlug', 'targetId');
    }

    /**
     * get slug
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function boardCategory()
    {
        return $this->hasOne('Xpressengine\Plugins\Board\Models\BoardCategory', 'targetId');
    }

    /**
     * get files
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(File::class, 'fileables', 'fileableId', 'fileId');
    }

    /**
     * get users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne('Xpressengine\User\Models\User', 'id', 'userId');
    }

    /**
     * get comments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Xpressengine\Comment\Models\Comment', 'targetId');
    }

    /**
     * get slug
     *
     * @return string
     */
    public function getSlug()
    {
        $slug = $this->boardSlug;
        return $slug === null ? '' : $slug->slug;
    }

    /**
     * get file ids
     *
     * @return array
     */
    public function getFileIds()
    {
        $files = $this->files;
        $ids = [];
        foreach ($files as $file) {
            $ids[] = $file->id;
        }
        return $ids;
    }

    /**
     * get favorite
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function favorite()
    {
        return $this->hasOne('Xpressengine\Plugins\Board\Models\BoardFavorite', 'targetId');
    }

    /**
     * 비회원이 작성 글 여부 반환
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->getAttribute('userType') === self::USER_TYPE_GUEST;
    }

    /**
     * Returns unique identifier
     *
     * @return string
     */
    public function getUid()
    {
        return $this->getAttribute('id');
    }

    /**
     * Returns instance identifier
     *
     * @return mixed
     */
    public function getInstanceId()
    {
        return $this->getAttribute('instanceId');
    }

    /**
     * Returns author
     *
     * @return \Xpressengine\User\UserInterface
     */
    public function getAuthor()
    {
        return $this->user ? : new Guest;
    }

    /**
     * Returns the link
     *
     * @param InstanceRoute $route route instance
     * @return string
     */
    public function getLink(InstanceRoute $route)
    {
        return $route->url . '/show/' . $this->getKey();
    }

    /**
     * visible
     *
     * @param $query
     */
    public function scopeVisible($query)
    {
        $query->where('status', Document::STATUS_PUBLIC)
            ->where('display', Document::DISPLAY_VISIBLE)
            ->where('published', Document::PUBLISHED_PUBLISHED);
    }
}
