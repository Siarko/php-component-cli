<?php


namespace Siarko\cli\util\profiler;


use MyCLabs\Enum\Enum;

/**
 * @method static TimeFactor SECONDS()
 * @method static TimeFactor MILLISECONDS()
 * @method static TimeFactor MICROSECONDS()
 * @method static TimeFactor NANOSECONDS()
 * */
class TimeFactor extends Enum
{
    private const SECONDS =       1000000000;
    private const MILLISECONDS =  1000000;
    private const MICROSECONDS =  1000;
    private const NANOSECONDS =   1;

}