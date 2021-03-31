<?php


namespace Siarko\cli\ui\layouts;

use Siarko\cli\bootstrap\exceptions\ParamTypeException;
use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\border\Padding;
use Siarko\cli\ui\components\ComponentFilter;
use Siarko\cli\util\BoundingBox;

abstract class AbstractLayout implements Layout
{
    /**
     * @var BaseComponent
     */
    protected BaseComponent $parentComponent;
    protected array $cache = [];

    /**
     * Flag for component validation -> invalidate to recalculate
     * */
    private bool $valid = false;

    protected function setCachedValue(BaseComponent $component, $value){
        $this->cache[$component->getUUID()] = $value;
    }

    protected function getCachedValue(BaseComponent $component, $default = null){
        if(!array_key_exists($component->getUUID(), $this->cache)){
            return $default;
        }
        return $this->cache[$component->getUUID()];
    }

    protected function flushCache(){
        $this->cache = [];
    }

    public function setParent(BaseComponent $component){
        $this->parentComponent = $component;
    }

    /**
     * Default child drawing
     * @param BaseComponent $component
     * @param array $children
     * @return mixed|void
     */
    public function drawChildren(BaseComponent $component, array $children)
    {
        /** @var BaseComponent $child */
        foreach ($children as $child) {
            $child->draw();
        }
    }

    /**
     * @param BaseComponent $component
     * @return Padding
     */
    public function getPadding(BaseComponent $component): Padding
    {
        return $this->parentComponent->getSizing()->getPadding();
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Invalidate to recalculate sizes
     * @param bool $flag
     * @return self
     */
    public function setValid(bool $flag): self
    {
        $this->valid = $flag;
        if(!$flag){
            $this->flushCache();
        }
        return $this;
    }

    /**
     * Strips inactive elements, modals and rewrites keys
     * @param BaseComponent $component
     * @return false|int
     */
    protected function getComponentIndex(BaseComponent $component){

        return array_search(
            $component,
            ComponentFilter::rewriteKeys(
                $this->parentComponent->getChildren(ComponentFilter::active(), ComponentFilter::floating(true))
            ),
            true
        );
    }

    /**
     * @param BoundingBox $target
     * @param Padding $padding
     * @return BoundingBox
     */
    protected function applyPadding(BoundingBox $target, Padding $padding): BoundingBox
    {
        return new BoundingBox(
            $target->getX()+$padding->getLeft(),
            $target->getY()+$padding->getUp(),
            $target->getX1()-$padding->getRight(),
            $target->getY1()-$padding->getDown(),
            true
        );
    }

}