<?php


namespace Siarko\cli\io\output\styles;


use Siarko\cli\paradigm\AbstractEnum;

class FontStyles extends AbstractEnum
{
    const STYLE_RESET = '0';
    const BOLD = '1';
    const DIM = '2';
    const ITALIC = '3';
    const UNDERLINE = '4';
    const DOUBLE_UNDERLINE = '21';
    const BLINK = '5';
    const STRIKE = '9';
    const INVERTED = '7';
}