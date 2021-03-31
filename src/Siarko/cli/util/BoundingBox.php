<?php


namespace Siarko\cli\util;


class BoundingBox
{
    private $x, $y, $w, $h;

    public function __construct($x, $y, $w, $h, $asCoords = false)
    {
        $this->x = $x;
        $this->y = $y;
        if ($asCoords) {
            $this->setX1($w);
            $this->setY1($h);
        } else {
            $this->w = $w;
            $this->h = $h;
        }
    }

    /**
     * @return mixed
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param mixed $x
     */
    public function setX($x): void
    {
        $this->x = $x;
    }

    /**
     * @return mixed
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param mixed $y
     */
    public function setY($y): void
    {
        $this->y = $y;
    }

    /**
     * @return Vec
     */
    public function getPosition(): Vec
    {
        return new Vec($this->getX(), $this->getY());
    }

    /**
     * @return Vec
     */
    public function getSize(): Vec
    {
        return new Vec($this->getWidth(), $this->getHeight());
    }

    public function setX1($x1)
    {
        $this->w = $x1 - $this->getX();
    }

    public function getX1()
    {
        return $this->getX() + $this->getWidth();
    }

    public function getY1()
    {
        return $this->getY() + $this->getHeight();
    }

    public function setY1($y1)
    {
        $this->h = $y1 - $this->getY();
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->w;
    }

    /**
     * @param mixed $w
     */
    public function setWidth($w): void
    {
        $this->w = $w;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->h;
    }

    /**
     * @param mixed $h
     */
    public function setHeight($h): void
    {
        $this->h = $h;
    }

    /**
     * @param BoundingBox $bb2
     * @return $this
     */
    public function shrink(BoundingBox $bb2): BoundingBox
    {
        $this->setX($this->getX()-$bb2->getX());
        $this->setY($this->getY()-$bb2->getY());
        $this->setX1($this->getX1() - $bb2->getX1());
        $this->setY1($this->getY1() - $bb2->getY1());
        return $this;
    }

    /**
     * @return BoundingBox
     */
    public function clone(): BoundingBox
    {
        return new BoundingBox(
            $this->x,
            $this->y,
            $this->w,
            $this->h
        );
    }


}