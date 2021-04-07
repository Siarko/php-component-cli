<?php


namespace Siarko\cli\io\output\styles;


use MyCLabs\Enum\Enum;

/**
 * @method static self BLACK()
 * @method static self RED()
 * @method static self GREEN()
 * @method static self YELLOW()
 * @method static self BLUE()
 * @method static self PURPLE()
 * @method static self CYAN()
 * @method static self LIGHT_GRAY()
 * @method static self WHITE()
 * */
class TextColor extends Enum
{
    private const BLACK = '30';
    private const RED = '31';
    private const GREEN = '32';
    private const YELLOW = '33';
    private const BLUE = '34';
    private const PURPLE = '35';
    private const CYAN = '36';
    private const LIGHT_GRAY = '37';
    private const WHITE = '38';
}