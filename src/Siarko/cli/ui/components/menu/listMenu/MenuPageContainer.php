<?php


namespace Siarko\cli\ui\components\menu\listMenu;


use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\ui\components\base\border\Border;
use Siarko\cli\ui\components\Container;

class MenuPageContainer extends Container
{

    private ListMenu $menuObject;

    public function draw()
    {
        $tmpBorder = $this->getBorder();
        if(!$this->getMenuObject()->isShowBreadcrumbs()){
            $this->setBorder(new Border());
        }
        parent::draw();
        $this->setBorder($tmpBorder);
    }


    public function _passEvent(KeyDownEvent $event): bool
    {
        if(!$this->isActive()){ return false; }
        foreach ($this->getChildren() as $child) {
            if ($child->getCustomFlag('selected') && $child->_receiveEvent($event)) {
                return true;
            }
        }
        foreach ($this->getChildren() as $child) {
            if ($child->getCustomFlag('selected') && $child->_passEvent($event)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ListMenu
     */
    public function getMenuObject(): ListMenu
    {
        return $this->menuObject;
    }

    /**
     * @param ListMenu $menuObject
     */
    public function setMenuObject(ListMenu $menuObject): void
    {
        $this->menuObject = $menuObject;
    }


}