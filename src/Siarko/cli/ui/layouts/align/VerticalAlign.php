<?php


namespace Siarko\cli\ui\layouts\align;


use MyCLabs\Enum\Enum;

/**
 * @method static self TOP()
 * @method static self MIDDLE()
 * @method static self BOTTOM()
 * */
class VerticalAlign extends Enum
{
    private const TOP = 0;
    private const MIDDLE = 1;
    private const BOTTOM = 2;
}