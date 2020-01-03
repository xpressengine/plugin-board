<?php
/**
 * BoardSlug
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

use Xpressengine\Database\Eloquent\DynamicModel;

/**
 * BoardSlug
 *
 * @property int id
 * @property string target_id
 * @property string instance_id
 * @property string slug
 * @property string title
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class BoardSlug extends DynamicModel
{
    static protected $reserved = [];

    protected $table = 'board_slug';
    public $timestamps = false;

    protected $fillable = [
        'target_id', 'slug', 'title', 'instance_id',
    ];

    /**
     * 예약어 추가
     * * 게시판 Routing 에 사용한는 이름은 슬러그로 사용할 수 없음
     *
     * @param string|array $slug slug
     * @return void
     */
    public static function setReserved($slug)
    {
        if (is_array($slug) === true) {
            self::$reserved = array_merge(self::$reserved, $slug);
        } else {
            self::$reserved[] = $slug;
        }
    }

    /**
     * convert title to slug
     * $title 을 ascii 코드로 변환 후 하이픈을 제외한 모든 특수문자 제거
     * 스페이스를 하이픈으로 변경
     *
     * @param string $title title
     * @param string $slug  slug
     * @return string
     */
    public static function convert($title, $slug = null)
    {
        // $slug 가 있다면 넘겨받은 slug 로 convert
        if ($slug != null) {
            $title = $slug;
        }
        $title = trim($title);

        // space change to dash
        $title = str_replace(' ', '-', $title);

        $slug = '';
        $len = mb_strlen($title);
        for ($i=0; $i<$len; $i++) {
            $ch = mb_substr($title, $i, 1);
            $code = static::utf8Ord($ch);

            if (($code <= 47 && $code != 45) ||
                ($code >= 58 && $code <= 64) ||
                ($code >= 91 && $code <= 96) ||
                ($code >= 123 && $code <= 127)) {
                continue;
            }
            $slug .= $ch;
        }

        // remove double dash
        $slug = str_replace('--', '-', $slug);

        return $slug;
    }

    /**
     * get ascii code
     *
     * @param string $ch character
     * @return bool|int
     */
    public static function utf8Ord($ch)
    {
        $len = strlen($ch);
        if ($len <= 0) {
            return false;
        }
        $h = ord($ch[0]);
        if ($h <= 0x7F) {
            return $h;
        }
        if ($h < 0xC2) {
            return false;
        }
        if ($h <= 0xDF && $len>1) {
            return ($h & 0x1F) <<  6 | (ord($ch[1]) & 0x3F);
        }
        if ($h <= 0xEF && $len>2) {
            return ($h & 0x0F) << 12 | (ord($ch[1]) & 0x3F) << 6 | (ord($ch[2]) & 0x3F);
        }
        if ($h <= 0xF4 && $len>3) {
            return ($h & 0x0F) << 18 | (ord($ch[1]) & 0x3F) << 12 | (ord($ch[2]) & 0x3F) << 6 | (ord($ch[3]) & 0x3F);
        }
        return false;
    }

    /**
     * make slug string
     *
     * @param string $slug slug
     * @param string $id   document id
     * @return string
     */
    public static function make($slug, $id)
    {
        $slug = static::convert($slug);

        $increment = 0;
        if (in_array($slug, self::$reserved) === true) {
            ++$increment;
        }

        while (static::has($slug, $increment) === true) {
            $slugInfo = static::where('slug', static::makeIncrement($slug, $increment))->first();
            if ($slugInfo->id == $id) {
                break;
            }

            ++$increment;
        }

        return static::makeIncrement($slug, $increment);
    }

    /**
     * 새로운 문자 생성
     *
     * @param string $slug      slug
     * @param int    $increment increment count
     * @return string
     */
    protected static function makeIncrement($slug, $increment)
    {
        if ($increment > 0) {
            $slug = $slug . '-' . $increment;
        }
        return $slug;
    }

    /**
     * has slug
     *
     * @param string $slug      slug
     * @param int    $increment increment count
     * @return int
     */
    public static function has($slug, $increment = 0)
    {
        $slug = static::makeIncrement($slug, $increment);

        $query = static::where('slug', $slug);
        $count = $query->count();
        if ($count !== 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get board
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function board()
    {
        return $this->hasOne(Board::class, 'id', 'target_id');
    }
}
