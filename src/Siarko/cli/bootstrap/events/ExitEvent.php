<?php


namespace Siarko\cli\bootstrap\events;


class ExitEvent implements EventProvider
{
    private $errorData;

    public function __construct($errorData = [])
    {
        $this->errorData = $errorData;
    }

    function getDataObject()
    {
        return new EventData(Events::EXIT, $this->errorData);
    }
}