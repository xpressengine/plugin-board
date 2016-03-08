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

use Xpressengine\Document\Models\Document;
use Xpressengine\Storage\File;

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
class Board extends Document
{
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

    public function files()
    {
        return $this->belongsToMany(File::class, 'fileables', 'fileableId', 'fileId');
    }

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

    public function getSlug()
    {
        $slug = $this->boardSlug;
        return $slug === null ? '' : $slug->slug;
    }

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
     * 비회원이 작성 글 여부 반환
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->getAttribute('userType') === self::USER_TYPE_GUEST;
    }
}
