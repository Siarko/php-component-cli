<?php


namespace Siarko\cli\ui\components;

use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\util\BoundingBox;

class Modal extends Container
{

    private ?BoundingBox $size = null;
    private ?\Closure $sizeGenerator = null;

    public function __construct()
    {
        parent::__construct();
        $this->setLayer(1000);
        $this->setFloating(true);
        $this->setVisible(false);
        $this->setBackgroundColor(BackgroundColor::TRANSPARENT());
    }

    /**
     * Modals are absolutely positioned - Always have screen BB as self BB
     * @return BoundingBox
     */
    protected function getBBAsChild(): BoundingBox
    {
        return $this->getSize();
    }


    /**
     * @return BoundingBox
     */
    public function getSize(): ?BoundingBox
    {
        if($this->sizeGenerator instanceof \Closure){
            $gen = $this->sizeGenerator;
            $this->size = $gen(parent::getScreenBB());
        }
        if (is_null($this->size)){
            $this->size = parent::getScreenBB();
        }
        return $this->size;
    }

    /**
     * @param BoundingBox|null $size
     * @return Modal
     */
    public function setSize(?BoundingBox $size): Modal
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Screen size (BoundingBox) is passed to generator during execution
     * @param \Closure $generator
     */
    public function setSizeGenerator(\Closure $generator){
        $this->sizeGenerator = $generator;
    }


}