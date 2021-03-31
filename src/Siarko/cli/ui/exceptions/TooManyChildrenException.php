<?php


namespace Siarko\cli\ui\exceptions;


use Throwable;

class TooManyChildrenException extends \Exception
{
    public function __construct($message = "Too many children supplied for component", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}