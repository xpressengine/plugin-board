<?php

namespace Xpressengine\Plugins\Board;

use Xpressengine\Config\{ConfigEntity, ConfigManager};

abstract class AbstractConfigHandler
{
    /**
     * config package name
     * 다른 모듈과 충돌을 피하기 위해 설정 이름을 모듈 이름으로 선언
     */
    const CONFIG_NAME = 'module/board@board';

    /** @var ConfigManager */
    protected $configManager;

    /** @var array */
    protected $defaultConfig = [];

    public function __construct(ConfigManager $configManager) {
        $this->configManager = $configManager;
    }

    public function getDefault(): ConfigEntity
    {
        $parent = $this->configManager->get(static::CONFIG_NAME);

        if (! ($parent instanceof ConfigEntity)) {
            $parent = $this->configManager->add(static::CONFIG_NAME, $this->defaultConfig);
        }

        return $parent;
    }

    public function getDefaultKeys(): array
    {
        return array_keys($this->defaultConfig);
    }

    public function existsDefault(): bool
    {
        $parent = $this->configManager->get(static::CONFIG_NAME);
        return $parent instanceof ConfigEntity;
    }

    public function addDefault(array $args): ConfigEntity
    {
        return $this->configManager->add(static::CONFIG_NAME, $args);
    }

    public function putDefault(array $args): ConfigEntity
    {
        return $this->configManager->put(static::CONFIG_NAME, $args);
    }

    public function add(array $params): ConfigEntity
    {
        return $this->configManager->add($this->name($params['boardId']), $params);
    }

    public function put(array $params): ConfigEntity
    {
        return $this->configManager->put($this->name($params['boardId']), $params);
    }

    public function modify(ConfigEntity $config): ConfigEntity
    {
        return $this->configManager->modify($config);
    }

    public function remove(ConfigEntity $config)
    {
        $this->configManager->remove($config);
    }

    public function gets(): array
    {
        $parent = $this->configManager->get(static::CONFIG_NAME);
        return $parent === null ? [] : $this->configManager->children($parent);
    }

    /**
     * @param string $boardId
     * @return ConfigEntity|null
     */
    public function get(string $boardId)
    {
       return $this->configManager->get($this->name($boardId));
    }

    protected function name(string $boardId): string
    {
        return sprintf('%s.%s', static::CONFIG_NAME, $boardId);
    }
}
