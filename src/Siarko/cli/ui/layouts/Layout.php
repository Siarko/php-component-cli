<?php


namespace Siarko\cli\ui\layouts;


use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\border\Padding;

Interface Layout
{

    /**
     * Can overwrite default child drawing implementation
     * @param BaseComponent $component
     * @param BaseComponent[] $children
     * @return mixed
     */
    public function drawChildren(BaseComponent $component, array $children);

    /**
     * Set Parent component for this layout
     * @param BaseComponent $component
     * @return mixed
     */
    public function setParent(BaseComponent $component);

    /**
     * Get BoundingBox for child component
     * @param BaseComponent $component
     * @return mixed
     */
    public function getBB(BaseComponent $component);

    /**
     * Calculate padding of parent for child component
     * @param BaseComponent $component
     * @return Padding
     */
    public function getPadding(BaseComponent $component): Padding;

}