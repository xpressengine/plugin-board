<?php

namespace Xpressengine\Plugins\Board;

use Illuminate\Support\Arr;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Plugins\Board\Models\Board;

class AnonymityHandler
{
    /**
     * @return static
     */
    public static function make()
    {
        return app(static::class);
    }

    /**
     * 추가 시 `익명` 처리.
     *
     * @param array $args
     * @param ConfigEntity $config
     */
    public function procWhenAdd(array &$args, ConfigEntity $config)
    {
        // 익명
        $anonymity = $config->get('anonymity');
        $allow = Arr::has($args, 'allow_anonymity');

        if ($this->isActivatedUse($anonymity) || ($allow && $this->isActivatedChoose($anonymity))) {
            $args['writer'] = $config->get('anonymityName');
            $args['user_type'] = Board::USER_TYPE_ANONYMITY;
        }
    }

    /**
     * 수정 시 ` 익명` 처리.
     *
     * @param array $args
     * @param Board $board
     * @param ConfigEntity $config
     * @depreciated
     */
    public function procWhenPut(array &$args, Board $board, ConfigEntity $config)
    {
        $anonymity = $config->get('anonymity');

        if ($this->isActivatedChoose($anonymity)) {
            $allow = Arr::has($args, 'allow_anonymity');
            $isGuest = $board->user_type  === Board::USER_TYPE_GUEST;

            $args['writer'] = $allow
                ? $config->get('anonymityName')
                : ($isGuest ? Arr::get($args, 'writer') : $board->getAttribute('user')->display_name);

            $args['user_type'] = $allow
                ? ($isGuest ? Board::USER_TYPE_GUEST : Board::USER_TYPE_ANONYMITY)
                : ($isGuest ? Board::USER_TYPE_GUEST : Board::USER_TYPE_USER);
        }
    }

    /**
     * @param $anonymity
     *
     * @return bool
     */
    public function isActivatedUse($anonymity)
    {
        return $anonymity === true || $anonymity === 'use';
    }

    /**
     * @param $anonymity
     *
     * @return bool
     */
    public function isActivatedDisuse($anonymity)
    {
        return $anonymity == false || $anonymity === 'disuse';
    }

    /**
     * @param $anonymity
     *
     * @return bool
     */
    public function isActivatedChoose($anonymity)
    {
        return $anonymity === 'choose';
    }
}
