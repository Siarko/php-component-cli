<?php


namespace Siarko\cli\ui\components;

use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\util\BoundingBox;

class Modal extends Container
{

    private ?BoundingBox $size = null;

    public function __construct($parent = null)
    {
        parent::__construct($parent);
        $this->setLayer(1000);
        $this->setFloating(true);
        $this->setVisible(false);
        $this->setBackgroundColor(new BackgroundColor(BackgroundColor::TRANSPARENT));
    }

    /**
     * Modals are absolutely positioned - Always have screen BB as self BB
     * @return BoundingBox
     */
    protected function getBBAsChild(): BoundingBox
    {
        return parent::getScreenBB();
    }


    /**
     * @return BoundingBox
     */
    public function getSize(): ?BoundingBox
    {
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

}