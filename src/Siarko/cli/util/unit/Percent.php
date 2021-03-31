<?php


namespace Siarko\cli\util\unit;


class Percent extends Unit
{
    public function __construct(float $value)
    {
        parent::__construct('%', $this);
        $this->set($value);
    }
}