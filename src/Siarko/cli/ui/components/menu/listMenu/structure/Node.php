<?php


namespace Siarko\cli\ui\components\menu\listMenu\structure;


use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\menu\listMenu\MenuPageContainer;

class Node
{
    /**
     * @var Node[]
     */
    protected array $children;
    /**
     * Main container
     * Specialized container that contains all item containers
     * @var MenuPageContainer|null
     */
    protected ?MenuPageContainer $container;

    /**
     * @var bool
     */
    private bool $valid = true;

    /**
     * @var BaseComponent
     */
    private BaseComponent $content;
    /**
     * Title of element - not used for internal logic, created based on content
     * @var string
     */
    private string $title;
    /**
     * Handler with action for item
     * @var \Closure
     */
    private \Closure $handler;


    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     */
    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    /**
     * @return BaseComponent
     */
    public function getContent(): BaseComponent
    {
        return $this->content;
    }

    /**
     * @param BaseComponent $content
     */
    public function setContent(BaseComponent $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return \Closure
     */
    public function getHandler(): \Closure
    {
        return $this->handler;
    }

    /**
     * @param \Closure $handler
     */
    public function setHandler(\Closure $handler): void
    {
        $this->handler = $handler;
    }

    /**
     * @return bool
     */
    public function hasHandler(): bool
    {
        return $this->handler instanceof \Closure;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function executeHandler($data){
        $handler = $this->handler;
        return $handler($data);
    }

    /**
     * @return Node[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    /**
     * @return int
     */
    public function getChildrenCount(): int
    {
        return count($this->getChildren());
    }

    /**
     * @param Node[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return MenuPageContainer
     */
    public function getContainer(): MenuPageContainer
    {
        return $this->container;
    }

    /**
     * @param MenuPageContainer|null $container
     */
    public function setContainer(?MenuPageContainer $container): void
    {
        $this->container = $container;
    }


}