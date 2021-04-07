<?php


namespace Siarko\cli\ui\layouts;


use Siarko\cli\bootstrap\exceptions\ParamTypeException;
use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\ComponentSizing;
use Siarko\cli\ui\components\ComponentFilter;
use Siarko\cli\ui\exceptions\TooManyChildrenException;
use Siarko\cli\ui\layouts\align\HorizontalAlign;
use Siarko\cli\ui\layouts\align\VerticalAlign;
use Siarko\cli\util\BoundingBox;
use Siarko\cli\util\UniVec;
use Siarko\cli\util\Vec;

class LayoutFill extends AbstractLayout
{

    //align options - of content does not fill entire component
    protected HorizontalAlign $horizontalAlign;
    protected VerticalAlign $verticalAlign;

    public function __construct()
    {
        $this->verticalAlign = VerticalAlign::MIDDLE();
        $this->horizontalAlign = HorizontalAlign::MIDDLE();
    }

    public function getBB(BaseComponent $component): BoundingBox
    {
        if(($result = $this->getCachedValue($component)) != null){
            return $result;
        }
        $bb = $this->parentComponent->getBB();
        $padding = $this->parentComponent->getSizing()->getPadding();
        $bounds = $this->applyPadding($bb, $padding);
        $maxSize = $component->getSizing()->calculateMaxSize($bounds->getSize());
        $value = $this->align($bounds, $maxSize);
        $this->setCachedValue($component, $value);
        return $value;
    }

    /**
     * @param BaseComponent $component
     * @param BaseComponent[] $children
     * @throws ParamTypeException
     * @throws TooManyChildrenException
     */
    public function drawChildren(BaseComponent $component, array $children)
    {
        $filtered = $this->parentComponent->getChildren(
            ComponentFilter::active(), ComponentFilter::floating(true)
        );
        $childCount = count($filtered);
        if($childCount == 0){
            return;
        }
        if($childCount > 1){
            throw new TooManyChildrenException();
        }else{
            $filtered[key($filtered)]->draw();
        }
    }

    /**
     * @param VerticalAlign $align
     * @return $this
     */
    public function setVerticalAlign(VerticalAlign $align): LayoutFill
    {
        $this->verticalAlign = $align;
        return $this;
    }

    /**
     * @param HorizontalAlign $align
     * @return $this
     */
    public function setHorizontalAlign(HorizontalAlign $align): LayoutFill
    {
        $this->horizontalAlign = $align;
        return $this;
    }

    /**
     * @return VerticalAlign
     */
    public function getVerticalAlign(): VerticalAlign
    {
        return $this->verticalAlign;
    }

    /**
     * @return HorizontalAlign
     */
    public function getHorizontalAlign(): HorizontalAlign
    {
        return $this->horizontalAlign;
    }

    /**
     * @param BoundingBox $maxBB
     * @param Vec $maxSize
     * @return BoundingBox
     */
    public function align(BoundingBox $maxBB, Vec $maxSize): BoundingBox
    {
        //horizontal
        if($maxSize->getX() != ComponentSizing::UNLIMITED_SIZE && $maxSize->getX() < $maxBB->getWidth()){
            $x1 = $maxBB->getX1();
            if($this->horizontalAlign->equals(HorizontalAlign::LEFT())){
                $maxBB->setWidth($maxSize->getX());
            }
            if($this->horizontalAlign->equals(HorizontalAlign::MIDDLE())){
                $o = (int)($maxBB->getWidth()/2)-(int)($maxSize->getX()/2)+$maxBB->getX();
                $maxBB->setX($o);
                $maxBB->setWidth($maxSize->getX());
            }
            if($this->horizontalAlign->equals(HorizontalAlign::RIGHT())){
                $maxBB->setX($x1-$maxSize->getX());
                $maxBB->setWidth($maxSize->getX());
            }
        }

        //Vertical
        if($maxSize->getY() != ComponentSizing::UNLIMITED_SIZE && $maxSize->getY() < $maxBB->getHeight()){
            $y1 = $maxBB->getY1();
            if($this->verticalAlign->equals(VerticalAlign::TOP())){
                $maxBB->setHeight($maxSize->getY());
            }
            if($this->verticalAlign->equals(VerticalAlign::MIDDLE())){
                $o = (int)($maxBB->getHeight()/2)-(int)($maxSize->getY()/2)+$maxBB->getY();
                $maxBB->setY($o);
                $maxBB->setHeight($maxSize->getY());
            }
            if($this->verticalAlign->equals(VerticalAlign::BOTTOM())){
                $maxBB->setY($y1-$maxSize->getY());
                $maxBB->setHeight($maxSize->getY());
            }
        }

        return $maxBB;
    }
}