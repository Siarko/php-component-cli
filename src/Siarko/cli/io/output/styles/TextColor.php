<?php


namespace Siarko\cli\io\output\styles;


use MyCLabs\Enum\Enum;

/**
 *  * @method static self BLACK()
 * @method static self RED()
 * @method static self GREEN()
 * @method static self YELLOW()
 * @method static self BLUE()
 * @method static self MAGENTA()
 * @method static self CYAN()
 * @method static self LIGHT_GRAY()
 * @method static self WHITE()
 * @method static self DARK_GRAY()
 * @method static self LIGHT_RED()
 * @method static self LIGHT_GREEN()
 * @method static self LIGHT_YELLOW()
 * @method static self LIGHT_BLUE()
 * @method static self LIGHT_MAGENTA()
 * @method static self LIGHT_CYAN()
 * @method static self DEFAULT()
 * */
class TextColor extends Color
{
    private const BLACK = '30';
    private const RED = '31';
    private const GREEN = '32';
    private const YELLOW = '33';
    private const BLUE = '34';
    private const MAGENTA = '35';
    private const CYAN = '36';
    private const LIGHT_GRAY = '37';
    private const WHITE = '38';
    private const DEFAULT = '39';
    private const DARK_GRAY = '90';
    private const LIGHT_RED = '91';
    private const LIGHT_GREEN = '92';
    private const LIGHT_YELLOW = '93';
    private const LIGHT_BLUE = '94';
    private const LIGHT_MAGENTA = '95';
    private const LIGHT_CYAN = '96';

}