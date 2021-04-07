<?php


namespace Siarko\cli\ui\components\base\border\lineBorder;


use MyCLabs\Enum\Enum;

/**
 * @method static self LINE_NORMAL()
 * @method static self LINE_BOLD()
 * @method static self DOTTED_NORMAL()
 * @method static self DOTTED_FINE_NORMAL()
 * @method static self DOTTED_BOLD()
 * @method static self DOTTED_FINE_BOLD()
 * @method static self DOUBLE_LINE()
 * */
class LineBorderVariant extends Enum
{
    const LINE_NORMAL = 0;
    const LINE_BOLD = 1;
    const DOTTED_NORMAL = 2;
    const DOTTED_FINE_NORMAL = 3;
    const DOTTED_BOLD = 4;
    const DOTTED_FINE_BOLD = 5;
    const DOUBLE_LINE = 6;

}