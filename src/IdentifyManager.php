<?php
/**
 * Board identify manager
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

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Session\SessionManager;
use Xpressengine\Document\DocumentEntity;
use Xpressengine\Document\DocumentHandler;

/**
 * Board identify manager
 * 비회원이 글을 작성 한 경우 그 글을 인증하기 위해 사용
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class IdentifyManager
{
    /**
     * 인증 후 결과를 저장 할 세션 이름
     */
    const SESSION_NAME = 'IDENTIFY_KEY';

    /**
     * 인증 세션의 만료 시간을 저장할 세션 이름
     */
    const EXPIRE_SESSION_NAME = 'IDENTIFY_KEY_EXPIRE_TIME';

    /**
     * 인증 세션 유지 시간.
     */
    const EXPIRE_TIME = 600;

    /**
     * @var SessionManager
     */
    protected $session;

    /**
     * @var DocumentHandler
     */
    protected $document;

    /**
     * @var Hasher
     */
    protected $hasher;

    /**
     * create instance
     *
     * @param SessionManager  $session  session manager
     * @param DocumentHandler $document document handler
     * @param Hasher          $hasher   hasher
     */
    public function __construct(SessionManager $session, DocumentHandler $document, Hasher $hasher)
    {
        $this->session = $session;
        $this->document = $document;
        $this->hasher = $hasher;
    }

    /**
     * 암호화 된 비밀번호 반환
     *
     * @param string $value password
     * @return string
     */
    public function hash($value)
    {
        return $this->hasher->make($value);
    }

    /**
     * 비회원 작성 글 인증 확인
     *
     * @param ItemEntity $item       board item entity
     * @param string     $email      email
     * @param string     $certifyKey 인증 암호
     * @return bool
     */
    public function verify(ItemEntity $item, $email, $certifyKey)
    {
        if ($email != $item->email) {
            return false;
        }
        return $this->hasher->check($certifyKey, $item->certifyKey);
    }

    /**
     * 한번 생성 한 세션은 EXPIRE_TIME 시간 만큼 유효함.
     *
     * @param string $id hashed certify key
     * @return string
     */
    public function getKey($id)
    {
        return self::SESSION_NAME . $id;
    }

    /**
     * 인증 세션 생성
     *
     * @param ItemEntity $item board item entity
     * @return void
     */
    public function create(ItemEntity $item)
    {
        $this->session->put($this->getKey($item->id), [
            'certifyKey' => $item->certifyKey,
            'expire' => $this->expireTime(),
        ]);
    }

    /**
     * get expire time
     *
     * @return int
     */
    private function expireTime()
    {
        return time() + self::EXPIRE_TIME;
    }

    /**
     * 인증 세션 반환
     *
     * @param ItemEntity $item board item entity
     * @return mixed
     */
    public function get(ItemEntity $item)
    {
        return $this->session->get($this->getKey($item->id));
    }

    /**
     * 문서에 대한 인증이 유효한지 검사
     * 인증 암호 및 유효 시간 검사
     *
     * @param ItemEntity $item board item entity
     * @return bool
     */
    public function validate(ItemEntity $item)
    {
        $session = $this->get($item);
        if ($item->certifyKey != $session['certifyKey']) {
            return false;
        }

        // 세션 만료됨
        if ($session['expire'] < time()) {
            $this->destroy($item);
            return false;
        }

        return true;
    }

    /**
     * 문서에 대해서 인증한 세션이 있는지 체크
     *
     * @param ItemEntity $item board item entity
     * @return bool
     */
    public function identified(ItemEntity $item)
    {
        $sessionName = $this->getKey($item->id);
        if ($this->session->has($sessionName) === false) {
            return false;
        }

        if ($this->validate($item) === false) {
            return false;
        }

        // 세션 갱신
        $this->destroy($item);
        $this->create($item);

        return true;
    }

    /**
     * destroy session
     *
     * @param ItemEntity $item board item entity
     * @return void
     */
    public function destroy(ItemEntity $item)
    {
        $this->session->remove($this->getKey($item->id));
    }
}
