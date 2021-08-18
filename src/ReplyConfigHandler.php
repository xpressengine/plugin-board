<?php

namespace Xpressengine\Plugins\Board;

use XeDocument;
use XeDynamicField;

class ReplyConfigHandler extends AbstractConfigHandler
{
    /**
     * config package name
     * 다른 모듈과 충돌을 피하기 위해 설정 이름을 모듈 이름으로 선언
     */
    const CONFIG_NAME = 'reply@module/board@board';

    /** @var array */
    protected $defaultConfig = [
        'protectUpdated' => false,      // 답글이 있으면 수정 불가
        'protectDeleted' => false,      // 답글이 있으면 삭제 불가
    ];

    public static function boot()
    {
        app()->singleton(ReplyConfigHandler::class, function() {
            return new ReplyConfigHandler(app('xe.config'));
        });

        app()->alias(ConfigHandler::class, 'xe.board.reply.config');
    }

    public static function make(): ReplyConfigHandler
    {
        return app(self::class);
    }
}
