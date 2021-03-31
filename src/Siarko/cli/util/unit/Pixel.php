<?php


namespace Siarko\cli\util\unit;


class Pixel extends Unit
{
    public function __construct(int $value)
    {
        parent::__construct('px', $this);
        $this->set($value);
    }

}