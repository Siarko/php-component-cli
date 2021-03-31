<?php


namespace Siarko\cli\util;


use MyCLabs\Enum\Enum;

abstract class KeyBindings extends Enum
{
    private array $bindings = [];

    public function __construct($value = null)
    {
        if(!is_null($value)){
            parent::__construct($value);
        }else{
            $this->init();
        }
    }

    protected abstract function init();

    public function setBinding(KeyBindings $bindingId, $keycodes){
        $this->bindings[$bindingId->getValue()] = $keycodes;
    }

    public function getBinding(KeyBindings $bindingId){
        return $this->bindings[$bindingId->getValue()];
    }

}