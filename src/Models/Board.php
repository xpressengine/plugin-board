<?php
/**
 * Board
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
namespace Xpressengine\Plugins\Board\Models;

use Xpressengine\Counter\Models\CounterLog;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Media\MediaManager;
use Xpressengine\Media\Models\Media;
use Xpressengine\Plugins\Comment\CommentUsable;
use Xpressengine\Plugins\Comment\Models\Comment;
use Xpressengine\Routing\InstanceRoute;
use Xpressengine\Seo\SeoUsable;
use Xpressengine\Storage\File;
use Xpressengine\Tag\Tag;
use Xpressengine\User\Models\Guest;
use Xpressengine\User\Models\UnknownUser;
use Xpressengine\User\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Board
 *
 * 게시판에서는 Document 모델을 확장해서 사용하기 위해 Board 모델 사용.
 * Board 모델에는 Document 에 없던 BoardCategory, BoardSlug 등 게시판을 위한 relation 을 추가.
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class Board extends Document implements CommentUsable, SeoUsable
{
    /**
     * get user id
     *
     * @return string
     */
    public function getUserId()
    {
        $userId = $this->getAttribute('user_id');
        if ($this->getAttribute('user_type') === self::USER_TYPE_ANONYMITY) {
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
     * get assent counter log
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assents()
    {
        return $this->hasMany(CounterLog::class, 'target_id')
            ->where('counter_name', 'vote')->where('counter_option', 'assent');
    }

    /**
     * get board data
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function boardData()
    {
        return $this->hasOne(BoardData::class, 'target_id');
    }

    /**
     * get slug
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function boardSlug()
    {
        return $this->hasOne(BoardSlug::class, 'target_id');
    }

    /**
     * get slug
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function boardCategory()
    {
        return $this->hasOne(BoardCategory::class, 'target_id');
    }

    /**
     * get files
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(File::class, 'fileables', 'fileable_id', 'file_id');
    }

    /**
     * get users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * get comments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'target_id');
    }

    /**
     * get tags
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'taggables', 'taggable_id', 'tag_id');
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
        return $this->belongsTo(BoardFavorite::class, 'id', 'target_id');
    }

    /**
     * get slug
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function slug()
    {
        return $this->belongsTo(BoardSlug::class, 'id', 'target_id');
    }

    /**
     * get slug
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data()
    {
        return $this->belongsTo(BoardData::class, 'id', 'target_id');
    }

    /**
     * 비회원이 작성 글 여부 반환
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->getAttribute('user_type') === self::USER_TYPE_GUEST;
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
        return $this->getAttribute('instance_id');
    }

    /**
     * Returns author
     *
     * @return \Xpressengine\User\UserInterface
     */
    public function getAuthor()
    {
        if ($this->user !== null) {
            return $this->user;
        } elseif ($this->isGuest() === true) {
            return new Guest;
        } else {
            return new UnknownUser;
        }
    }

    /**
     * has user
     *
     * @return bool
     */
    public function hasAuthor()
    {
        return $this->user !== null;
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
     * @param Builder $query query
     * @return $this
     */
    public function scopeVisible(Builder $query)
    {
        $query->where('status', static::STATUS_PUBLIC)
            ->whereIn('display', [static::DISPLAY_VISIBLE, static::DISPLAY_SECRET])
            ->where('published', static::PUBLISHED_PUBLISHED);
    }

    /**
     * notice
     *
     * @param Builder $query query
     * @return $this
     */
    public function scopeNotice(Builder $query)
    {
        $query->where('status', static::STATUS_NOTICE)
            ->whereIn('display', [static::DISPLAY_VISIBLE, static::DISPLAY_SECRET])
            ->where('published', static::PUBLISHED_PUBLISHED);
    }

    /**
     * Returns title
     *
     * @return string
     */
    public function getTitle()
    {
        $title = str_replace('"', '\"', $this->getAttribute('title'));

        return $title;
    }

    /**
     * get compiled content
     *
     * @return string
     */
    public function getContent()
    {
        return compile($this->instance_id, $this->content, $this->format === static::FORMAT_HTML);
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return str_replace(
            ['"', "\n"],
            ['\"', ''],
            $this->getAttribute('pure_content')
        );
    }

    /**
     * Returns keyword
     *
     * @return string|array
     */
    public function getKeyword()
    {
        return [];
    }

    /**
     * Returns url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getSlug();
    }

    /**
     * Returns image url list
     *
     * @return array
     */
    public function getImages()
    {
        $files = File::getByFileable($this->getKey());

        /** @var MediaManager $mediaManager */
        $mediaManager = app('xe.media');
        $imageHandler = $mediaManager->getHandler(Media::TYPE_IMAGE);

        $images = [];
        foreach ($files as $file) {
            if ($mediaManager->getFileType($file) === Media::TYPE_IMAGE) {
                $images[] = $imageHandler->make($file);
            }
        }
        return $images;
    }

    /**
     * get array
     *
     * @return array
     */
    public function toArray()
    {
        $this->slug;
        $this->boardData;
        $this->boardCategory;
        $this->files;
        $this->tags;
        $this->user;

        return parent::toArray();
    }
}
