<?php


namespace Siarko\cli\ui\components\menu\listMenu;


use Siarko\cli\io\output\StyledText;
use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\border\lineBorder\LineBorder;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\ui\components\Container;
use Siarko\cli\ui\components\TextComponent;
use Siarko\cli\ui\layouts\align\HorizontalAlign;
use Siarko\cli\ui\layouts\LayoutHorizontal;
use Siarko\cli\ui\layouts\LayoutVertical;
use Siarko\cli\util\Cacheable;
use Siarko\cli\util\unit\Pixel;

class MenuStructure
{
    use Cacheable;

    private array $structure;
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

    private function validateStructure(array $structure): array
    {
        if(empty($structure)){ return []; }
        $menuStructure = $this->_validateStructure($structure);
        $container = $this->menu->createContainer($menuStructure);
        if($this->isShowBreadcrumbs()){
            $this->setContainerTitle($container, "/");
        }
        return [
            ListMenu::SUBMENU => $menuStructure,
            ListMenu::CONTAINER => $container
        ];
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
            $row = [];
            if(is_string($data) || $data instanceof Component){
                $data = [ListMenu::CONTENT => $data];
            }
            if (is_array($data)){
                if(array_key_exists(ListMenu::CONTENT, $data)){
                    $c = $data[ListMenu::CONTENT];
                    if(is_string($c)){
                        $row[ListMenu::CONTENT] = new TextComponent($c);
                    }elseif ($c instanceof Component){
                        $row[ListMenu::CONTENT] = $c;
                    }
                    $row[ListMenu::TITLE] = $this->getTitle($c, $id);
                }else{
                    $row[ListMenu::CONTENT] = new TextComponent($id);
                    $row[ListMenu::TITLE] = $this->getTitle($id);
                }
                if(array_key_exists(ListMenu::SUBMENU, $data) && is_array($data[ListMenu::SUBMENU])){
                    $b = $breadcrumbs."/".$row[ListMenu::TITLE];
                    $row[ListMenu::SUBMENU] = $this->_validateStructure($data[ListMenu::SUBMENU], $b);

                    $container = $this->menu->createContainer($row[ListMenu::SUBMENU]);
                    if($this->isShowBreadcrumbs()){
                        $this->setContainerTitle($container, $b);
                    }
                    $row[ListMenu::CONTAINER] = $container;
                }else{
                    $row[ListMenu::SUBMENU] = null;
                }
                if(array_key_exists(ListMenu::HANDLER, $data) && is_callable($data[ListMenu::HANDLER])){
                    $row[ListMenu::HANDLER] = $data[ListMenu::HANDLER];
                }
            }
            $result[$id] = $row;
        }
        return $result;
    }

    /**
     * Get menu data by path
     * @param string $menuPath
     * @return array
     */
    public function getData(string $menuPath): array
    {
        if(strlen($menuPath) == 0){
            return $this->structure;
        }
        if($this->cacheExists($menuPath)){
            return $this->getCache($menuPath);
        }
        $path = explode('/', $menuPath);
        $current = &$this->structure[ListMenu::SUBMENU];
        $p = &$this->structure;
        foreach ($path as $part) {
            $p = &$current[$part];
            $current = &$current[$part][ListMenu::SUBMENU];
        }
        $this->setCache($menuPath, $p);
        return $p;
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


}