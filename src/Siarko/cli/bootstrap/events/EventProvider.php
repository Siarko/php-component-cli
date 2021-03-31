<?php


namespace Siarko\cli\bootstrap\events;


interface EventProvider
{
    /**
     * @return EventData
     */
    function getDataObject();


}