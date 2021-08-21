<?php

namespace Xpressengine\Plugins\Board;

use XeDocument;
use XeDynamicField;
use Xpressengine\Config\ConfigEntity;

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

    /**
     * boot reply config handler
     */
    public static function boot()
    {
        app()->singleton(ReplyConfigHandler::class, function() {
            return new ReplyConfigHandler(app('xe.config'));
        });

        app()->alias(ConfigHandler::class, 'xe.board.reply.config');
    }

    /**
     * make reply config handler
     *
     * @return ReplyConfigHandler
     */
    public static function make(): ReplyConfigHandler
    {
        return app(self::class);
    }

    /**
     * get config entity
     *
     * @param string $boardId
     * @return ConfigEntity
     */
    public function get(string $boardId): ConfigEntity
    {
        $name = $this->name($boardId);
        $config = $this->configManager->get($name);

        if (! ($config instanceof ConfigEntity)) {
            if (!$this->existsDefault()) {
                $this->getDefault();
            }

            return $this->configManager->add($name, []);
        }

        return $config;
    }

    /**
     * get activated reply configs
     *
     * @return array
     */
    public function getActivateds(): array
    {
        $configs = [];
        $boardConfigs = app('xe.board.config')->gets();

        foreach ($boardConfigs as $boardConfig) {
            if ($boardConfig->get('replyPost', false) === true) {
                array_push($configs, ReplyConfigHandler::make()->get($boardConfig->get('boardId')));
            }
        }

        return $configs;
    }

    /**
     * get activated ids
     *
     * @return array
     */
    public function getActivatedIds(): array
    {
        return array_map(
            function($activated) { return $activated->get('boardId'); },
            $this->getActivateds()
        );
    }

    /**
     * get activated reply configs
     *
     * @param string $boardId
     * @return \Xpressengine\Config\ConfigEntity|null
     */
    public function getActivated(string $boardId)
    {
        $config = app('xe.board.config')->get($boardId);
        return ($config !== null && $config->get('replyPost', false)) ? ReplyConfigHandler::make()->get($boardId) : null;
    }
}
