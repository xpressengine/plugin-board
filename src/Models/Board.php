<?php
/**
 * Board
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
namespace Xpressengine\Plugins\Board\Models;

use Illuminate\Support\Collection;
use Xpressengine\Counter\Models\CounterLog;
use Xpressengine\Document\Models\Document;
use Xpressengine\Media\MediaManager;
use Xpressengine\Media\Models\Media;
use Xpressengine\Plugins\Board\Handler;
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
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class Board extends Document implements CommentUsable, SeoUsable
{
    protected $casts = [
        'status' => 'int',
        'approved' => 'int',
        'published' => 'int',
        'display' => 'int',
        'format' => 'int',
        'created_at' => 'datetime',
    ];

    /**
     * Canonical url
     *
     * @var string
     */
    protected $canonical;
    
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
        return strtotime($this->getAttribute(static::CREATED_AT)) + ($hour * 3600) > time();
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
        $file = new File;

        return $this->belongsToMany(File::class, $file->getFileableTable(), 'fileable_id', 'file_id')
            ->withPivot('created_at')
            ->orderBy('pivot_' . 'created_at', 'asc');
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
     * get writer name
     *
     * @return string
     */
    public function getDisplayWriterName()
    {
        if ($this->isGuest()) {
            return $this->getAttribute('writer');
        } else {
            return $this->user->getDisplayName();
        }
    }

    /**
     * get comments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->belongsToMany(Comment::class, 'comment_target', 'target_id', 'doc_id');
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
     * get favorites
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoriteUsers()
    {
        return $this->belongsToMany(User::class, 'board_favorites', 'target_id', 'user_id');
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
     * get thumbnail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thumb()
    {
        return $this->belongsTo(BoardGalleryThumb::class, 'id', 'target_id');
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
     * has author
     *
     * @return bool
     */
    public function hasAuthor(): bool
    {
        return $this->getAttribute('user') !== null;
    }

    /**
     * is anonymity
     *
     * @return bool
     */
    public function isAnonymity(): bool
    {
        return $this->user_type === Board::USER_TYPE_ANONYMITY;
    }

    /**
     * is not anonymity
     *
     * @return bool
     */
    public function isNotAnonymity(): bool
    {
        return $this->isAnonymity() === false;
    }

    /**
     * is notice
     *
     * @return bool
     */
    public function isNotice(): bool
    {
        return $this->status == static::STATUS_NOTICE;
    }

    /**
     * is adopted
     *
     * @param Board $parent
     * @return bool
     */
    public function isAdopted(Board $parent = null): bool
    {
        if (is_null($parent)) {
            $parent = $this->hasParentDoc() ? Board::with('data', 'replies')->find($this->parent_id) : null;
        }

        return ($parent->getAttribute('data')->adopt_id ?? null) == $this->id;
    }

    /**
     * has adopted
     *
     * @return bool
     */
    public function hasAdopted(): bool
    {
        $adoptedId = $this->getAttribute('data')->adopt_id ?? null;

        return $this->getReplies()->contains(function(Board $reply) use($adoptedId) {
            return $reply->id == $adoptedId;
        });
    }

    /**
     * replies
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Board::class, 'parent_id', 'id');
    }

    /**
     * get replies
     *
     * @return Collection
     */
    public function getReplies(): Collection
    {
        return $this->getAttribute('replies');
    }

    /**
     * exists replies
     *
     * @return bool
     */
    public function existsReplies(): bool
    {
        return $this->getReplies()->count() > 0;
    }

    /**
     * find parent doc
     *
     * @return Board|null
     */
    public function findParentDoc()
    {
        if ($this->hasParentDoc()) {
            /** @var Board $parentDoc */
            $parentDoc = Board::division($this->instance_id)->find($this->parent_id);

            if ($parentDoc instanceof Board) {
                return $parentDoc->load('replies', 'data');
            }
        }

        return null;
    }

    /**
     * has parent doc
     *
     * @return bool
     */
    public function hasParentDoc(): bool
    {
        return $this->parent_id !== '';
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
            ->where('published', static::PUBLISHED_PUBLISHED)
            ->where(function($query){
                $query->where('approved',static::APPROVED_APPROVED)
                    ->orWhere($this->getTable().'.user_id',auth()->id());
            });
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
     * visible with notice
     *
     * @param Builder $query query
     * @return void
     */
    public function scopeVisibleWithNotice(Builder $query)
    {
        $query->whereIn('status', [static::STATUS_PUBLIC, static::STATUS_NOTICE])
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
        return $this->canonical;
    }

    /**
     * Set canonical url
     *
     * @param string $url url
     * @return $this
     */
    public function setCanonical($url)
    {
        $this->canonical = $url;

        return $this;
    }

    /**
     * Returns image url list
     *
     * @return array
     */
    public function getImages()
    {
        $thumb = app(Handler::class)->getThumb($this->getKey());

        $images = [];
        if ($thumb && $thumb->board_thumbnail_file_id == '') {
            $path = $thumb->board_thumbnail_external_path ?
                $thumb->board_thumbnail_external_path :
                $thumb->board_thumbnail_path;
            if ($path) {
                $images[] = $path;
            }
        } elseif ($thumb) {
            $file = \XeStorage::find($thumb->board_thumbnail_file_id);
            /** @var MediaManager $mediaManager */
            $mediaManager = app('xe.media');
            $imageHandler = $mediaManager->getHandler(Media::TYPE_IMAGE);
            if ($file !== null && $mediaManager->getFileType($file) === Media::TYPE_IMAGE) {
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

    /**
     * get display status name
     *
     * @param int $displayCode display status code
     *
     * @return string
     */
    public function getDisplayStatusName($displayCode)
    {
        $displayName = [
            self::DISPLAY_HIDDEN => 'board::displayStatusHidden',
            self::DISPLAY_SECRET => 'board::displayStatusSecret',
            self::DISPLAY_VISIBLE => 'board::displayStatusVisible'
        ];

        return $displayName[$displayCode];
    }

    /**
     * get approve status name
     *
     * @param int $approveCode approve status code
     *
     * @return string
     */
    public function getApproveStatusName($approveCode)
    {
        $approveName = [
            self::APPROVED_REJECTED => 'board::approveStatusRejected',
            self::APPROVED_WAITING => 'board::approveStatusWaiting',
            self::APPROVED_APPROVED => 'board::approveStatusApproved'
        ];

        return $approveName[$approveCode];
    }
}
