<?php


namespace Siarko\cli\ui\components\menu\listMenu\structure;


use Siarko\cli\ui\components\menu\listMenu\MenuPageContainer;

class RootNode extends Node
{
    /**
     * RootNode constructor.
     * @param Node[] $children
     * @param MenuPageContainer|null $container
     */
    public function __construct(array $children = [], MenuPageContainer $container = null)
    {
        $this->setChildren($children);
        $this->setContainer($container);
        if(empty($children)){
            $this->setValid(false);
        }
    }
}