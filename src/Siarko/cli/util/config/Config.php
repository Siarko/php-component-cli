<?php


namespace Siarko\cli\util\config;


use Siarko\cli\bootstrap\Bootstrap;
use Siarko\cli\bootstrap\events\Events;
use Siarko\cli\util\ArrayHelper;

abstract class Config
{
    private $data = [];

    protected abstract function getPath(): string;
    protected abstract function getDefault(): array;
    protected abstract function getStructure(): ?array;

    public function __construct()
    {
        if(file_exists($this->getPath())){
            //load config if file exists
            $this->load(file_get_contents($this->getPath()));
        }else{
            //file n/e, create and save
            $this->load(null);
            $this->save();
        }

        Bootstrap::get()->setProcess(function(){
            //Save config on app close
            $this->save();
        }, Events::EXIT);

    }

    public function load(?string $configString){
        if(is_null($configString)){
            $this->data = $this->getDefault();
        }else{
            $this->data = json_decode($configString, true);
            $structureModel = $this->getStructure() ?? $this->getDefault();
            $this->validateStructure($this->data, $structureModel);
        }
    }

    public function save(){
        if(!file_exists($this->getPath())){
            $fileName = basename($this->getPath());
            $this->mkpath(substr($this->getPath(),0, strlen($this->getPath())-strlen($fileName)));
        }
        file_put_contents($this->getPath(), $this->toString());
    }

    private function toString(){
        return json_encode($this->data);
    }

    public function getValue($path = null){
        return ArrayHelper::getValue($this->data, $path);
    }

    private function validateStructure(&$target, $model){
        foreach ($model as $key => $value) {
            if(!array_key_exists($key, $target)){
                $target[$key] = $model[$key];
            }else{
                if(is_array($value)){
                    $this->validateStructure($target[$key], $model[$key]);
                }
            }
        }
    }

    private function mkpath($path){
        if(@mkdir($path) || file_exists($path)){
            return true;
        }
        return $this->mkpath(dirname($path)) && mkdir($path);
    }

}