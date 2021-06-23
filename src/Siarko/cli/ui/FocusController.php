<?php


namespace Siarko\cli\ui;


use Siarko\cli\ui\components\base\BaseComponent;

class FocusController
{
    private ?BaseComponent $lastFocused = null;
    private ?BaseComponent $currentlyFocused = null;

    /**
     * Switch focus
     * @param BaseComponent $component
     */
    public function focus(BaseComponent $component){
        if(!is_null($this->getCurrentlyFocused())){
            $this->getCurrentlyFocused()->unFocus();
            $this->setLastFocused($this->getCurrentlyFocused());
        }
        $this->setCurrentlyFocused($component);
        $component->focus();
    }

    public function back(){
        if(!is_null($this->getLastFocused())){
            $this->focus($this->getLastFocused());
        }
    }

    /**
     * @return BaseComponent|null
     */
    public function getLastFocused(): ?BaseComponent
    {
        return $this->lastFocused;
    }

    /**
     * @param BaseComponent|null $lastFocused
     */
    private function setLastFocused(?BaseComponent $lastFocused): void
    {
        $this->lastFocused = $lastFocused;
    }

    /**
     * @return BaseComponent|null
     */
    public function getCurrentlyFocused(): ?BaseComponent
    {
        return $this->currentlyFocused;
    }

    /**
     * @param BaseComponent|null $currentlyFocused
     */
    public function setCurrentlyFocused(?BaseComponent $currentlyFocused): void
    {
        $this->currentlyFocused = $currentlyFocused;
    }
}