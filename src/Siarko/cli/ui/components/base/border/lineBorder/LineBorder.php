<?php


namespace Siarko\cli\ui\components\base\border\lineBorder;


use Siarko\cli\ui\components\base\border\Border;

class LineBorder extends Border
{
    private const VARIANTS = [
        LineBorderVariant::LINE_NORMAL => ['│','─','┌','┐','┘','└'],
        LineBorderVariant::LINE_BOLD => ['┃','━','┏','┓','┛','┗'],
        LineBorderVariant::DOTTED_NORMAL => ['┆','┄','┌','┐','┘','└'],
        LineBorderVariant::DOTTED_FINE_NORMAL => ['┊','┈','┌','┐','┘','└'],
        LineBorderVariant::DOTTED_BOLD => ['┇','┅','┏','┓','┛','┗'],
        LineBorderVariant::DOTTED_FINE_BOLD => ['┋','┉','┏','┓','┛','┗'],
        LineBorderVariant::DOUBLE_LINE => ['║','═','╔','╗','╝','╚']
    ];

    public function __construct(LineBorderVariant $variant = null)
    {
        parent::__construct();

        if(is_null($variant)){ $variant = new LineBorderVariant(); }
        $variant = $variant->getValue();

        $this->setLeftUp(self::VARIANTS[$variant][2]);
        $this->setLeftDown(self::VARIANTS[$variant][5]);
        $this->setRightUp(self::VARIANTS[$variant][3]);
        $this->setRightDown(self::VARIANTS[$variant][4]);

        $this->setUp(self::VARIANTS[$variant][1]);
        $this->setDown(self::VARIANTS[$variant][1]);
        $this->setLeft(self::VARIANTS[$variant][0]);
        $this->setRight(self::VARIANTS[$variant][0]);
    }
}