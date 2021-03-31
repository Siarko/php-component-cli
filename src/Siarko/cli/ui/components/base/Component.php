<?php


namespace Siarko\cli\ui\components\base;


use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\output\styles\TextColor;
use Siarko\cli\ui\components\base\border\Border;
use Siarko\cli\ui\components\base\border\Margin;
use Siarko\cli\ui\components\base\border\Padding;

abstract class Component extends BaseComponent
{

    private array $keyDownHandlers = [];
    /**
     * @param Border $border
     * @return self
     */
    public function setBorder(Border $border): Component
    {
        $this->getSizing()->setBorder($border);
        return $this;
    }

    /**
     * @param $color
     * @return $this
     */
    public function setBorderColor(TextColor $color): Component
    {
        $this->getSizing()->getBorder()->setColor($color);
        return $this;
    }

    /**
     * @param Padding $padding
     * @return self
     */
    public function setPadding(Padding $padding): Component
    {
        $this->getSizing()->setPadding($padding);
        return $this;
    }

    /**
     * setMargin(1) -> 1 for all
     * setMargin([UP => 1, DOWN => 2])
     * @param Margin $margin
     * @return self
     */
    public function setMargin(Margin $margin): Component
    {
        $this->getSizing()->setMargin($margin);
        return $this;
    }

    /**
     * @return Margin
     */
    public function getMargin(): Margin
    {
        return $this->getSizing()->getMargin();
    }

    /**
     * @return Border
     */
    public function getBorder(): Border
    {
        return $this->getSizing()->getBorder();
    }

    /**
     * @return Padding
     */
    public function getPadding(): Padding
    {
        return $this->getSizing()->getPadding();
    }

    /* KEYBOARD EVENT HANDLING*/

    /**
     * Consume passed event, should be overwritten if component uses keyboard input
     * Should return bool -> should event be consumed
     * @param KeyDownEvent $event
     * @return bool
     */
    protected function onKeyDown(KeyDownEvent $event): bool
    {
        return false;
    }

    /**
     * Receive event and call processor function
     * Called before passEvent
     * Called by View instance or parent component
     * @param KeyDownEvent $event
     * @return bool
     */
    public function _receiveEvent(KeyDownEvent $event): bool
    {
        if($this->hasFocus() || $this->hasPermanentFocus()){
            foreach ($this->getKeyDownHandlers() as $keyDownHandler) {
                //if event handler returns true -> Event is consumed
                if($keyDownHandler($event)){
                    return true;
                }
            }
            return $this->onKeyDown($event);
        }else{
            return false;
        }
    }

    /**
     * Pass event data to this component children
     * Called after current layer consumes eventData
     * Called by View instance or parent component
     * @param KeyDownEvent $event
     * @return bool
     */
    public function _passEvent(KeyDownEvent $event): bool
    {
        if(!$this->isActive()){ return false; }
        foreach ($this->getChildren() as $child) {
            if ($child->_receiveEvent($event)) {
                return true;
            }
        }
        foreach ($this->getChildren() as $child) {
            if ($child->_passEvent($event)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getKeyDownHandlers(): array
    {
        return $this->keyDownHandlers;
    }

    /**
     * @param callable $keyDownHandler
     */
    public function addKeyDownHandler(callable $keyDownHandler): void
    {
        $this->keyDownHandlers[] = $keyDownHandler;
    }

}