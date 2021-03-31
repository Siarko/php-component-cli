<?php


namespace Siarko\cli\util\exceptions;


use Throwable;

class TypesNotUniformException extends \Exception
{

    public function __construct($message = "Supplied types are not uniform!", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}