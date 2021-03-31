<?php


namespace Siarko\cli\ui\exceptions;


use Throwable;

class NoMainComponentException extends \Exception
{
    public function __construct($message = "No main component supplied for the View", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}