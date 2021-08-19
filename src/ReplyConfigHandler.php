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
        'protectUpdated' => false,      // 답글이 있으면 수정할 수 없도록 합니다. (if, true)
        'protectDeleted' => false,      // 답글이 있으면 삭제할 수 없도록 합니다. (if, true)
        'blockAuthorSelf' => false,     // 작성자 스스로 답글을 작성하지 못하도록 합니다. (if, true)
        'limitedOneTime' => false,      // 답변 작성은 한 게시물 당 한 번으로 제한합니다. (if, true)
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

    public function getByBoardConfig(string $boardId)
    {
        $config = app('xe.board.config')->get($boardId);

        if (is_null($config)) {
            return null;
        }

        return $config->get('replyPost', false) ? ReplyConfigHandler::make()->get($boardId) : null;
    }
}
