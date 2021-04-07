<?php


namespace Siarko\cli\io\output\styles;


use MyCLabs\Enum\Enum;

/**
 * @method static self STYLE_RESET()
 * @method static self BOLD()
 * @method static self DIM()
 * @method static self ITALIC()
 * @method static self UNDERLINE()
 * @method static self DOUBLE_UNDERLINE()
 * @method static self BLINK()
 * @method static self STRIKE()
 * @method static self INVERTED()
 * */
class FontStyles extends Enum
{
    private const STYLE_RESET = '0';
    private const BOLD = '1';
    private const DIM = '2';
    private const ITALIC = '3';
    private const UNDERLINE = '4';
    private const DOUBLE_UNDERLINE = '21';
    private const BLINK = '5';
    private const STRIKE = '9';
    private const INVERTED = '7';
}