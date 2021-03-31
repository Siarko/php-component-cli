<?php


namespace Siarko\cli\bootstrap\events;


use Siarko\cli\paradigm\Singleton;

/**
 * @method static self get()
 * */
class InitEvent implements EventProvider
{
    use Singleton;

    private $initialized = false;

    function getDataObject()
    {
        if($this->initialized){
            return null;
        }else{
            $this->initialized = true;
            return new EventData(Events::INIT, []);
        }
    }
}