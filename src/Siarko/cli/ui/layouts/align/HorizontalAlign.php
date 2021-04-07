<?php


namespace Siarko\cli\ui\layouts\align;


use MyCLabs\Enum\Enum;

/**
 * @method static self LEFT()
 * @method static self MIDDLE()
 * @method static self RIGHT()
 * */
class HorizontalAlign extends Enum
{
    private const LEFT = 0;
    private const MIDDLE = 1;
    private const RIGHT = 2;
}