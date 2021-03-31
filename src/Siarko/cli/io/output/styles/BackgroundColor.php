<?php


namespace Siarko\cli\io\output\styles;


use Siarko\cli\paradigm\AbstractEnum;

class BackgroundColor extends AbstractEnum
{
    const BLACK = '40';
    const RED = '41';
    const GREEN = '42';
    const YELLOW = '43';
    const BLUE = '44';
    const PURPLE = '45';
    const CYAN = '46';
    const LIGHT_GRAY = '47';
    const DARK_GRAY = '100';
    const LIGHT_RED = '101';
    const LIGHT_GREEN = '102';
    const LIGHT_YELLOW = '103';
    const LIGHT_BLUE = '104';
    const LIGHT_PURPLE = '105';
    const LIGHT_CYAN = '106';
    const WHITE = '107';
    const TRANSPARENT = -1;

    public function __construct($initial_value = self::BLACK, $strict = false)
    {
        parent::__construct($initial_value, $strict);
    }
}