<?php


namespace Siarko\cli\util;

use Siarko\cli\util\exceptions\TypesNotUniformException;

/**
 * Universal vector - can contain anything
 * */
class UniVec
{
    public $x;
    public $y;

    public function __construct($x = null, $y = null)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getX()
    {
        return $this->x;
    }

    public function setX($x): UniVec
    {
        $this->x = $x;
        return $this;
    }

    public function getY()
    {
        return $this->y;
    }

    public function setY($y): UniVec
    {
        $this->y = $y;
        return $this;
    }

    /**
     * @return false|string
     * @throws TypesNotUniformException
     */
    public function getType(): string
    {
        $t1 = $this->getTypeX();
        if ($t1 != $this->getTypeY()) {
            throw new TypesNotUniformException();
        }
        return $t1;
    }

    public function getTypeX(){
        $t = gettype($this->getX());
        if($t == 'object'){
            return get_class($this->getX());
        }
        return $t;
    }

    public function getTypeY(){
        $t = gettype($this->getY());
        if($t == 'object'){
            return get_class($this->getY());
        }
        return $t;
    }

}