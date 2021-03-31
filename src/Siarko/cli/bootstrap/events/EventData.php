<?php


namespace Siarko\cli\bootstrap\events;


class EventData
{
    private $state;
    private $arguments;

    public function __construct(
        $state, $arguments
    )
    {
        $this->state = $state;
        $this->arguments = $arguments;
        if(!is_array($this->arguments)){
            $this->arguments = [$this->arguments];
        }
    }

    public function getState(){
        return $this->state;
    }

    public function getProcessorArguments(){
        return $this->arguments;
    }

}