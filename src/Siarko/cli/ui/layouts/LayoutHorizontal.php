<?php


namespace Siarko\cli\ui\layouts;

use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\border\Padding;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\ui\components\ComponentFilter;
use Siarko\cli\ui\exceptions\IncorrectProportionsException;
use Siarko\cli\ui\exceptions\TooFewProportionValues;
use Siarko\cli\util\BoundingBox;
use Siarko\cli\util\unit\Pixel;

class LayoutHorizontal extends DividedLayout
{

    private int $spacing = 0;

    public function setSpacing(int $spacing): LayoutHorizontal
    {
        $this->spacing = $spacing;
        return $this;
    }

    public function getSpacing(): int
    {
        return $this->spacing;
    }

    /**
     * @param BaseComponent $component
     * @return BoundingBox
     * @throws TooFewProportionValues
     */
    public function getBB(BaseComponent $component): BoundingBox
    {
        if(($result = $this->getCachedValue($component)) != null){
            return $result;
        }
        $parentBB = $this->applyPadding(
            $this->parentComponent->getBB(),
            $this->getPadding($component)
        );
        //calculate value and offset
        $this->calculateValuesForComponent($component, $parentBB->getWidth());
        $result = new BoundingBox(
            $parentBB->getX() + $this->offset,
            $parentBB->getY(),
            $this->value,
            $parentBB->getHeight()
        );
        $this->setCachedValue($component, $result);
        return $result;
    }

    protected function getComponentBorderSize(Component $c): int
    {
        return $c->getBorder()->collapseLeft() + $c->getBorder()->collapseRight();
    }
}