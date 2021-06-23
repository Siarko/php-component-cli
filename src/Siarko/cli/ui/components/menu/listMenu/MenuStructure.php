<?php


namespace Siarko\cli\ui\components\menu\listMenu;

use Siarko\cli\io\output\StyledText;
use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\border\lineBorder\LineBorder;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\ui\components\menu\listMenu\structure\Node;
use Siarko\cli\ui\components\menu\listMenu\structure\RootNode;
use Siarko\cli\ui\components\menu\listMenu\structure\UnexpectedMenuStructureFormat;
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

    public function set($structure)
    {
        if (is_array($structure)) {
            $this->valid = !empty($structure);
        }else{
            $this->valid = true;
        }
        $this->cachePurge();
        $this->structure = $this->validateStructure($structure);
    }

    /**
     * @throws UnexpectedMenuStructureFormat
     * @throws \Siarko\cli\ui\exceptions\IncorrectProportionsException
     */
    private function validateStructure($structure): RootNode
    {
        $result = null;
        if(is_array($structure)){
            if (empty($structure)) {
                return new RootNode();
            }
            $menuStructure = $this->_validateArrayStructure($structure);
            $container = $this->menu->createContainer($menuStructure);
            $result = new RootNode($menuStructure, $container);
        }elseif ($structure instanceof RootNode){
            if(is_null($structure->getContainer())){
                $this->_validateObjectStructure($structure->getChildren());
                $container = $this->menu->createContainer($structure->getChildren());
                /*if ($this->isShowBreadcrumbs()) {
                    $this->setContainerTitle($container, "/");
                }*/
                $structure->setContainer($container);
            }
            $result = $structure;
        }else{
            throw new UnexpectedMenuStructureFormat(
                "Menu structure must be an array or RootNode, ".gettype($structure).' provided'
            );
        }
        if ($this->isShowBreadcrumbs()) {
            $this->setContainerTitle($container, "/");
        }

        return $result;
    }

    /**
     * Parse menu structure and normalize it
     * @param array $structure
     * @param string $breadcrumbs
     * @return array
     * @throws \Siarko\cli\ui\exceptions\IncorrectProportionsException
     */
    private function _validateArrayStructure(array $structure, $breadcrumbs = ""): array
    {
        $result = [];
        foreach ($structure as $id => $data) {
            $node = new Node();
            if (is_string($data) || $data instanceof Component) {
                $data = [ListMenu::CONTENT => $data];
            }
            if (is_array($data)) {
                if (array_key_exists(ListMenu::CONTENT, $data)) {
                    $c = $data[ListMenu::CONTENT];
                    if (is_string($c)) {
                        $node->setContent($this->getTextComponent($c));
                    } elseif ($c instanceof Component) {
                        $node->setContent($c);
                    }
                    $node->setTitle($this->getTitle($c, $id));
                } else {
                    $node->setContent($this->getTextComponent($id));
                    $node->setTitle($this->getTitle($id));
                }
                if (array_key_exists(ListMenu::SUBMENU, $data) && is_array($data[ListMenu::SUBMENU])) {
                    $b = $breadcrumbs . "/" . $node->getTitle();
                    $node->setChildren($this->_validateArrayStructure($data[ListMenu::SUBMENU], $b));

                    $container = $this->menu->createContainer($node->getChildren());
                    if ($this->isShowBreadcrumbs()) {
                        $this->setContainerTitle($container, $b);
                    }
                    $node->setContainer($container);
                } else {
                    $node->setChildren([]);
                }
                if (array_key_exists(ListMenu::HANDLER, $data) && is_callable($data[ListMenu::HANDLER])) {
                    $node->setHandler($data[ListMenu::HANDLER]);
                }
            }
            $result[$id] = $node;
        }
        return $result;
    }

    /**
     * @param Node[] $structure
     * @throws \Siarko\cli\ui\exceptions\IncorrectProportionsException
     */
    private function _validateObjectStructure(array $structure, string $breadcrumbs = '')
    {
        foreach ($structure as $child) {
            $content = $child->getContent();
            if(strlen($child->getTitle()) == 0 && $content instanceof TextComponent){
                /** @var TextComponent $content */
                $child->setTitle($content->getStyledText()->getText());
            }
            if($child->hasChildren()){
                $b = $breadcrumbs . "/" . $child->getTitle();
                $this->_validateObjectStructure($child->getChildren());
                $container = $this->menu->createContainer($child->getChildren());
                if ($this->isShowBreadcrumbs()) {
                    $this->setContainerTitle($container, $b);
                }
                $child->setContainer($container);
            }
        }

    }

    /**
     * Get menu data by path
     * @param string $menuPath
     * @return Node
     */
    public function getData(string $menuPath)
    {
        if (strlen($menuPath) == 0) {
            return $this->structure;
        }
        if ($this->cacheExists($menuPath)) {
            return $this->getCache($menuPath);
        }
        $path = explode('/', $menuPath);
        $current = $this->structure->getChildren();
        $result = null;
        foreach ($path as $part) {
            $result = &$current[$part];
            if (array_key_exists($part, $current)) {
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

    protected function setContainerTitle(Component $container, string $title)
    {
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
        if (is_string($data)) {
            $result = $data;
        }
        if ($data instanceof BaseComponent) {
            if ($data instanceof TextComponent) {
                $result = $data->getStyledText()->getText();
            } elseif ($data instanceof StyledText) {
                $result = $data->getText();
            } elseif (method_exists($data, '__toString')) {
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