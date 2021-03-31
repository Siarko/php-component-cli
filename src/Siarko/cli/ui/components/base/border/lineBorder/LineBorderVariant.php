<?php


namespace Siarko\cli\ui\components\base\border\lineBorder;


use Siarko\cli\paradigm\AbstractEnum;

class LineBorderVariant extends AbstractEnum
{
    const LINE_NORMAL = 0;
    const LINE_BOLD = 1;
    const DOTTED_NORMAL = 2;
    const DOTTED_FINE_NORMAL = 3;
    const DOTTED_BOLD = 4;
    const DOTTED_FINE_BOLD = 5;
    const DOUBLE_LINE = 6;

    public function __construct($initial_value = self::LINE_NORMAL, $strict = false)
    {
        parent::__construct($initial_value, $strict);
    }

}