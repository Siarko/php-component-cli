<?php


namespace Siarko\cli\io\input;

use Siarko\cli\bootstrap\Bootstrap;
use Siarko\cli\bootstrap\events\EventData;
use Siarko\cli\bootstrap\events\EventProvider;
use Siarko\cli\bootstrap\events\Events;
use Siarko\cli\paradigm\Singleton;
use Siarko\cli\io\input\event\BootstrapKeyEvents;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\input\stream\Input;

/**
 * @method static self get()
 * */
class Keyboard implements EventProvider
{

    use Singleton;

    private $inputStream = null;

    /**
     * @var KeyDownEvent[]
     */
    private array $outsideEvents = [];

    private function __construct()
    {
        $this->inputStream = new Input();
        Bootstrap::get()->setProcess(function(){
            $this->inputStream->close();
        }, Events::EXIT);
    }

    public function pauseStream(){
        $this->inputStream->releaseStream();
    }

    public function catchStream(){
        $this->inputStream->catchStream();
    }

    public function addOutsideEvent(KeyDownEvent $event){
        $this->outsideEvents[] = $event;
    }

    private function isEventAvailable(): bool
    {
        return !$this->inputStream->isBufferEmpty() || count($this->outsideEvents) > 0;
    }

    /**
     * Return stream or outside event
     * outside events are first in queue
     * @return EventData|KeyDownEvent
     */
    private function getEvent(){
        if(count($this->outsideEvents) > 0){
            $event = array_shift($this->outsideEvents);
        }else{
            $bytes = $this->inputStream->getBufferContent();
            $event = new KeyDownEvent($bytes);
            if($event->isChar() && count($event->getAllCodes()) > 1 && $event->getCode() <= 127){
                $this->inputStream->setBufferOverflow($event->getAllCodes()[1]);
                $event = new KeyDownEvent([$event->getCode()]);
            }
        }
        return new EventData(BootstrapKeyEvents::KEYUP, [$event]);
    }

    /**
     * @inheritDoc
     */
    function getDataObject()
    {
        if($this->isEventAvailable()){
            return $this->getEvent();
        }else{
            return new EventData(Events::IDLE, []);
        }
    }
}