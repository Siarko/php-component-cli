<?php


namespace Siarko\cli\util\unit;


use Throwable;

class UnknownUnitException extends \Exception
{
    public function __construct($message = "Unknown unit supplied!", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}