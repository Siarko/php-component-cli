<?php


namespace Siarko\cli\util;


class HandlerQueue
{
    private $queue = [];

    public function add(callable $handler){
        $this->queue[] = $handler;
    }

    public function execute($data, $cancelable = false){
        foreach ($this->queue as $handler) {
            if(!$handler($data) && $cancelable){
                break;
            }
        }
    }

}