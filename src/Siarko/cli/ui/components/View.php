<?php


namespace Siarko\cli\ui\components;

use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\ui\exceptions\NoMainComponentException;
use Siarko\cli\util\profiler\Profiler;

abstract class View
{
    private $id;
    private bool $valid = false;
    private ?Component $mainComponent = null;

    /**
     * @throws NoMainComponentException
     * @throws \Siarko\cli\bootstrap\exceptions\ParamTypeException
     */
    private function drawWrapper(){
        if($this->mainComponent){
            $this->mainComponent->draw();
        }else{
            throw new NoMainComponentException();
        }
    }

    public function setId(int $id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function draw(){
        if(!$this->isValid()){
            Profiler::start('draw_view');
            $this->drawWrapper();
            Profiler::end();
        }
    }

    public function update(){
        $this->setValid(false);
        $this->mainComponent->setValid(false);
    }

    public function setValid(bool $flag){
        $this->valid = $flag;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function receiveKeyboardEvent(KeyDownEvent $eventData)
    {
        if($this->mainComponent){
            if(!$this->mainComponent->_receiveEvent($eventData)){
                $this->mainComponent->_passEvent($eventData);
            }
        }else{
            throw new NoMainComponentException();
        }
    }

    public function setMainComponent(Component $component){
        $this->mainComponent = $component;
    }

}