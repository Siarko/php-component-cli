<?php


namespace Siarko\cli\ui\layouts;


use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\border\Padding;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\ui\components\ComponentFilter;
use Siarko\cli\ui\exceptions\TooFewProportionValues;
use Siarko\cli\util\BoundingBox;
use Siarko\cli\util\unit\Pixel;

class LayoutVertical extends DividedLayout
{
    private int $spacing = 0;

    public function setSpacing(int $spacing): LayoutVertical
    {
        $this->spacing = $spacing;
        return $this;
    }

    public function getSpacing(): int
    {
        return $this->spacing;
    }

    public function getBB(BaseComponent $component): BoundingBox
    {
        if(($result = $this->getCachedValue($component)) != null){
            return $result;
        }
        $parentBB = $this->applyPadding(
            $this->parentComponent->getBB(),
            $this->getPadding($component)
        );

        $this->calculateValuesForComponent($component, $parentBB->getHeight());

        $result = new BoundingBox(
            $parentBB->getX(),
            $parentBB->getY()+$this->offset,
            $parentBB->getWidth(),
            $this->value
        );
        $this->setCachedValue($component, $result);
        return $result;
    }


    protected function getComponentBorderSize(Component $c): int
    {
        return $c->getBorder()->collapseTop() + $c->getBorder()->collapseBottom();
    }
}