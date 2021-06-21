<?php


namespace Siarko\cli\ui\components\menu\listMenu;

use Siarko\cli\io\output\StyledText;
use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\border\lineBorder\LineBorder;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\ui\components\menu\listMenu\structure\Node;
use Siarko\cli\ui\components\menu\listMenu\structure\RootNode;
use Siarko\cli\ui\components\TextComponent;
use Siarko\cli\util\Cacheable;

class MenuStructure
{
    use Cacheable;

    private RootNode $structure;
    private bool $valid = true;

    /**
     * @var bool
     */
    private bool $showBreadcrumbs = true;

    private ListMenu $menu;

    public function __construct(ListMenu $menu)
    {
        $this->menu = $menu;
    }

    public function set(array $structure){
        $this->valid = !empty($structure);
        $this->cachePurge();
        $this->structure = $this->validateStructure($structure);
    }

    private function validateStructure(array $structure): RootNode
    {
        if(empty($structure)){ return new RootNode(); }
        $menuStructure = $this->_validateStructure($structure);
        $container = $this->menu->createContainer($menuStructure);
        if($this->isShowBreadcrumbs()){
            $this->setContainerTitle($container, "/");
        }
        return new RootNode($menuStructure, $container);
    }

    /**
     * Parse menu structure and normalize it
     * @param array $structure
     * @param string $breadcrumbs
     * @return array
     * @throws \Siarko\cli\ui\exceptions\IncorrectProportionsException
     */
    private function _validateStructure(array $structure, $breadcrumbs = ""): array
    {
        //TODO change this from assoc array to object based approach
        $result = [];
        foreach ($structure as $id => $data) {
            $node = new Node();
            if(is_string($data) || $data instanceof Component){
                $data = [ListMenu::CONTENT => $data];
            }
            if (is_array($data)){
                if(array_key_exists(ListMenu::CONTENT, $data)){
                    $c = $data[ListMenu::CONTENT];
                    if(is_string($c)){
                        $node->setContent($this->getTextComponent($c));
                    }elseif ($c instanceof Component){
                        $node->setContent($c);
                    }
                    $node->setTitle($this->getTitle($c, $id));
                }else{
                    $node->setContent($this->getTextComponent($id));
                    $node->setTitle($this->getTitle($id));
                }
                if(array_key_exists(ListMenu::SUBMENU, $data) && is_array($data[ListMenu::SUBMENU])){
                    $b = $breadcrumbs."/".$node->getTitle();
                    $node->setChildren($this->_validateStructure($data[ListMenu::SUBMENU], $b));

                    $container = $this->menu->createContainer($node->getChildren());
                    if($this->isShowBreadcrumbs()){
                        $this->setContainerTitle($container, $b);
                    }
                    $node->setContainer($container);
                }else{
                    $node->setChildren([]);
                }
                if(array_key_exists(ListMenu::HANDLER, $data) && is_callable($data[ListMenu::HANDLER])){
                    $node->setHandler($data[ListMenu::HANDLER]);
                }
            }
            $result[$id] = $node;
        }
        return $result;
    }

    /**
     * Get menu data by path
     * @param string $menuPath
     * @return Node
     */
    public function getData(string $menuPath): Node
    {
        if(strlen($menuPath) == 0){
            return $this->structure;
        }
        if($this->cacheExists($menuPath)){
            return $this->getCache($menuPath);
        }
        $path = explode('/', $menuPath);
        $current = $this->structure->getChildren();
        $result = null;
        foreach ($path as $part) {
            $result = &$current[$part];
            if(array_key_exists($part, $current)){
                $current = $current[$part]->getChildren();
            }
        }
        $this->setCache($menuPath, $result);
        return $result;
    }

    /**
     * @return bool
     */
    public function isShowBreadcrumbs(): bool
    {
        return $this->showBreadcrumbs;
    }

    /**
     * @param bool $showBreadcrumbs
     * @return MenuStructure
     */
    public function setShowBreadcrumbs(bool $showBreadcrumbs): MenuStructure
    {
        //value was changed
        $this->showBreadcrumbs = $showBreadcrumbs;
        return $this;
    }

    protected function setContainerTitle(Component $container, string $title){
        $container->setBorder((new LineBorder())->setTitle($title));
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    private function getTitle($data, $default = 'undefined')
    {
        $result = $default;
        if(is_string($data)){
            $result = $data;
        }
        if($data instanceof BaseComponent){
            if($data instanceof TextComponent){
                $result = $data->getText()->getText();
            }elseif($data instanceof StyledText){
                $result = $data->getText();
            }elseif(method_exists($data, '__toString')){
                $result = $data->__toString();
            }
        }

        return $result;

    }

    /**
     * @param $text
     * @return TextComponent
     */
    private function getTextComponent($text): TextComponent
    {
        return new TextComponent($text);
    }


}