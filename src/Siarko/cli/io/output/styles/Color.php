<?php


namespace Siarko\cli\io\output\styles;


use MyCLabs\Enum\Enum;

/**
 * @method static self BLACK()
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
 * */
class Color extends Enum
{
    private const BLACK = 'BLACK';
    private const RED = 'RED';
    private const GREEN = 'GREEN';
    private const YELLOW = 'YELLOW';
    private const BLUE = 'BLUE';
    private const MAGENTA = 'MAGENTA';
    private const CYAN = 'CYAN';
    private const LIGHT_GRAY = 'LIGHT_GRAY';
    private const WHITE = 'WHITE';
    private const DARK_GRAY = 'DARK_GRAY';
    private const LIGHT_RED = 'LIGHT_RED';
    private const LIGHT_GREEN = 'LIGHT_GREEN';
    private const LIGHT_YELLOW = 'LIGHT_YELLOW';
    private const LIGHT_BLUE = 'LIGHT_PURPLE';
    private const LIGHT_MAGENTA = 'LIGHT_MAGENTA';
    private const LIGHT_CYAN = 'LIGHT_CYAN';

    public function getBackground(): BackgroundColor{
        $name = $this->getKey();
        return BackgroundColor::$name();
    }

    /**
     * @return mixed
     */
    public function getTextColor(): TextColor{
        $name = $this->getKey();
        return TextColor::$name();
    }

}