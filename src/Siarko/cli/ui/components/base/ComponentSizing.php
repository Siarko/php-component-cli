<?php


namespace Siarko\cli\ui\components\base;


use Siarko\cli\ui\components\base\border\Border;
use Siarko\cli\ui\components\base\border\Margin;
use Siarko\cli\ui\components\base\border\Padding;
use Siarko\cli\util\unit\Pixel;
use Siarko\cli\util\unit\Unit;
use Siarko\cli\util\UniVec;
use Siarko\cli\util\Vec;

class ComponentSizing
{

    const UNLIMITED_SIZE = -1;

    private Border $border;
    private Padding $padding;
    private Margin $margin;

    //max size of component without border in Unit vector
    private UniVec $maxSizing;

    public function __construct()
    {
        $this->setMargin(new Margin());
        $this->setPadding(new Padding());
        $this->setBorder(new Border());
        $this->maxSizing = new UniVec(new Pixel(self::UNLIMITED_SIZE), new Pixel(self::UNLIMITED_SIZE));
    }


    public function setMaxSize(?Unit $x = null, ?Unit $y = null): ComponentSizing
    {
        if(!is_null($x)){
            $this->maxSizing->setX($x);
        }
        if(!is_null($y)){
            $this->maxSizing->setY($y);
        }
        return $this;
    }

    /**
     * @return UniVec
     */
    public function getMaxSize(): UniVec
    {
        return $this->maxSizing;
    }

    /**
     * @param Vec $bounds
     * @return Vec
     */
    public function calculateMaxSize(Vec $bounds): Vec
    {
        $borders = $this->calculateBorders();
        $maxSize = $this->getMaxSize();
        if($maxSize->getTypeX() == Pixel::class){
            if($maxSize->getX()->getValue() == self::UNLIMITED_SIZE){
                $w = $bounds->getX();
            }else{
                $w = $maxSize->getX()->getValue();
            }
        }else{
            $w = (int)($bounds->getX() * ($maxSize->getX()->getValue()/100));
        }
        if($maxSize->getTypeY() == Pixel::class){
            if($maxSize->getY()->getValue() == self::UNLIMITED_SIZE){
                $h = $bounds->getY();
            }else{
                $h = $maxSize->getY()->getValue();
            }
        }else{
            $h = (int)($bounds->getY() * ($maxSize->getY()->getValue()/100));
        }
        return new Vec(
            $borders->getX()+$w,
            $borders->getY()+$h
        );
    }

    /**
     * Calculate size of borders
     * @return Vec
     */
    public function calculateBorders(): Vec
    {
        $borders = $this->getBorder();
        $width = $borders->collapseLeft()+$borders->collapseRight();
        $height = $borders->collapseTop()+$borders->collapseBottom();
        return new Vec($width, $height);
    }

    /**
     * @param Border $border
     * @return self
     */
    public function setBorder(Border $border): ComponentSizing
    {
        $this->border = $border;
        return $this;
    }

    /**
     * @param Padding $padding
     * @return self
     */
    public function setPadding(Padding $padding): ComponentSizing
    {
        $this->padding = $padding;
        return $this;
    }

    /**
     * setMargin(1) -> 1 for all
     * setMargin([UP => 1, DOWN => 2])
     * @param Margin $margin
     * @return self
     */
    public function setMargin(Margin $margin): ComponentSizing
    {
        $this->margin = $margin;
        return $this;
    }

    /**
     * @return Margin
     */
    public function getMargin(): Margin
    {
        return $this->margin;
    }

    /**
     * @return Border
     */
    public function getBorder(): Border
    {
        return $this->border;
    }

    /**
     * @return Padding
     */
    public function getPadding(): Padding
    {
        $calculatedPadding = new Padding();
        $calculatedPadding->setRight($this->padding->getRight() + $this->border->collapseRight());
        $calculatedPadding->setLeft($this->padding->getLeft() + $this->border->collapseLeft());
        $calculatedPadding->setUp($this->padding->getUp() + $this->border->collapseTop());
        $calculatedPadding->setDown($this->padding->getDown() + $this->border->collapseBottom());
        return $calculatedPadding;
    }
}