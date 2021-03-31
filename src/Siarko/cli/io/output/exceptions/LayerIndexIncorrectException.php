<?php


namespace Siarko\cli\io\output\exceptions;


use Throwable;

class LayerIndexIncorrectException extends \Exception
{
    public function __construct($message = "Layer id is incorrect!", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}