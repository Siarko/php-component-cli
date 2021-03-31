<?php


namespace Siarko\cli\util;


class Vec
{
    public $x;
    public $y;

    public function __construct($x = 0, $y = 0)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @param int $x
     */
    public function setX(int $x): void
    {
        $this->x = $x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @param int $y
     */
    public function setY(int $y): void
    {
        $this->y = $y;
    }

    /**
     * @param $x
     * @param int $y
     * @param bool $toSelf
     * @return $this
     */
    public function increment($x, $y = 0, $toSelf = true): Vec
    {
        if ($toSelf) {
            $this->x += $x;
            $this->y += $y;
            return $this;
        } else {
            return new Vec($this->getX() + $x, $this->getY() + $y);
        }
    }


}