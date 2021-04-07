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
 * @method static self DARK_GRAY()
 * @method static self LIGHT_RED()
 * @method static self LIGHT_GREEN()
 * @method static self LIGHT_YELLOW()
 * @method static self LIGHT_BLUE()
 * @method static self LIGHT_PURPLE()
 * @method static self LIGHT_CYAN()
 * @method static self WHITE()
 * @method static self TRANSPARENT()
 *
 * */
class BackgroundColor extends Enum
{
    private const BLACK = '40';
    private const RED = '41';
    private const GREEN = '42';
    private const YELLOW = '43';
    private const BLUE = '44';
    private const PURPLE = '45';
    private const CYAN = '46';
    private const LIGHT_GRAY = '47';
    private const DARK_GRAY = '100';
    private const LIGHT_RED = '101';
    private const LIGHT_GREEN = '102';
    private const LIGHT_YELLOW = '103';
    private const LIGHT_BLUE = '104';
    private const LIGHT_PURPLE = '105';
    private const LIGHT_CYAN = '106';
    private const WHITE = '107';
    private const TRANSPARENT = -1;
}