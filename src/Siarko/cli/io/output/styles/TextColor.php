<?php


namespace Siarko\cli\io\output\styles;


use Siarko\cli\paradigm\AbstractEnum;

class TextColor extends AbstractEnum
{
    const BLACK = '30';
    const RED = '31';
    const GREEN = '32';
    const YELLOW = '33';
    const BLUE = '34';
    const PURPLE = '35';
    const CYAN = '36';
    const LIGHT_GRAY = '37';
    const WHITE = '38';

    public function __construct($initial_value = self::WHITE, $strict = false)
    {
        parent::__construct($initial_value, $strict);
    }
}