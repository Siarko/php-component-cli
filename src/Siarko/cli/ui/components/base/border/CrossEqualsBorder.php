<?php


namespace Siarko\cli\ui\components\base\border;


class CrossEqualsBorder extends Border
{

    public function __construct()
    {
        parent::__construct();
        $this->setLeftUp('+');
        $this->setLeftDown('+');
        $this->setRightUp('+');
        $this->setRightDown('+');

        $this->setUp('=');
        $this->setDown('=');
        $this->setLeft('|');
        $this->setRight('|');
    }
}