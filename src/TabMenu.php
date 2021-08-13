<?php

namespace Xpressengine\Plugins\Board;

use Illuminate\Support\Str;

class TabMenu
{
    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var int */
    private $ordering = 0;

    /** @var \Closure */
    private $linkFunction;

    /** @var \Closure|mixed */
    private $content;

    /** @var boolean */
    private $isExternalLink = false;

    /** @var string */
    private $icon;

    /** @var bool */
    private $display = true;

    public static function make(array $data = null): TabMenu
    {
        $tabMenu = new static();
        return $data !== null ? $tabMenu->fill($data) : $tabMenu;
    }

    public function fill(array $data): TabMenu
    {
        foreach ($data as $key => $value) {
            $method = 'set' . Str::studly($key);

            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }

        return $this;
    }

    public function is(TabMenu $menu): bool
    {
        return $this->getId() === $menu->getId();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $value): TabMenu
    {
        $this->id = $value;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $value): TabMenu
    {
        $this->title = $value;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): TabMenu
    {
        $this->description = $description;
        return $this;
    }

    public function getOrdering(): int
    {
        return $this->ordering;
    }

    public function setOrderNumber(int $number): TabMenu
    {
        $this->ordering = $number;
        return $this;
    }

    public function getLinkFunction(): \Closure
    {
        return $this->linkFunction;
    }

    public function setLinkFunction(\Closure $function): TabMenu
    {
        $this->linkFunction = $function;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content): TabMenu
    {
        $this->content = $content;
        return $this;
    }

    public function getIsExternalLink(): bool
    {
        return $this->isExternalLink;
    }

    public function setIsExternalLink(bool $isActivate): TabMenu
    {
        $this->isExternalLink = $isActivate;
        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon(string $icon): TabMenu
    {
        $this->icon = $icon;
        return $this;
    }

    public function getDisplay(): bool
    {
        return $this->display;
    }

    public function setDisplay(bool $isDisplay): TabMenu
    {
        $this->display = $isDisplay;
        return $this;
    }
}
