<?php
declare(strict_types=1);

namespace Siarko\cli\ui\components\menu\listMenu;

use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\ui\components\Container;
use Siarko\cli\ui\components\menu\listMenu\structure\Node;
use Siarko\cli\ui\components\TextComponent;
use Siarko\cli\ui\exceptions\IncorrectProportionsException;
use Siarko\cli\ui\layouts\align\HorizontalAlign;
use Siarko\cli\ui\layouts\LayoutFill;
use Siarko\cli\ui\layouts\LayoutHorizontal;
use Siarko\cli\ui\layouts\LayoutVertical;
use Siarko\cli\util\BoundingBox;
use Siarko\cli\util\profiler\Profiler;
use Siarko\cli\util\unit\Pixel;

class ListMenu extends Component
{

    const CONTENT = 'content';
    const SUBMENU = 'submenu';
    const HANDLER = 'handler';
    const DISABLED = 'disabled';

    //Structure passed bu user and parsed
    private MenuStructure $structure;
    //path of selected menu
    private ?string $menuPath;
    private BaseComponent $lastSelected;
    private Component $lastContainer;
    private ?BackgroundColor $selectionColor = null;

    private int $lastHeight = -1;
    //item window - for item overflow
    private int $itemWindowStart = 0;

    private MenuKeyBindings $keyBindings;


    public function __construct(array $structure = [])
    {
        parent::__construct();

        $this->keyBindings = new MenuKeyBindings();
        $this->structure = new MenuStructure($this);
        $this->setLayout(new LayoutFill());
        $this->setStructure($structure);
        $this->setSelectionColor(BackgroundColor::DARK_GRAY());

    }

    public function drawContent(BoundingBox $contentBox)
    {
        $height = $contentBox->getHeight();
        if ($this->getStructure()->isShowBreadcrumbs()) {
            $height -= 2;
        }
        $this->hideMenuItems($height);
    }

    /**
     * @return MenuStructure
     */
    public function getStructure(): MenuStructure
    {
        return $this->structure;
    }

    /**
     * @param array $structure
     * @return ListMenu
     */
    public function setStructure(array $structure): ListMenu
    {
        $this->abortAllChildren();
        $this->structure->set($structure);
        if (empty($structure)) {
            return $this;
        }
        $this->addContainers($this->structure->getData(''));
        return $this;
    }

    /**
     * Return fill path of currently selected Item
     * @param bool $stripLast - Remove last part after slash
     * @return string
     */
    public function getMenuPath(bool $stripLast = false): string
    {
        if ($stripLast) {
            $slashPos = strrpos($this->menuPath, '/');
            if($slashPos === false){
                return '';
            }
            return substr($this->menuPath, 0, $slashPos);
        } else {
            return $this->menuPath;
        }
    }

    /**
     * Return ID of currently selected item
     * @return string
     */
    public function getSelectedItem(): string
    {
        $slashPos = strrpos($this->menuPath, '/');
        if($slashPos === false){
            return $this->menuPath;
        }
        return trim(substr($this->menuPath, $slashPos), '/');
    }

    /**
     * @param ?string $menuPath
     * @param bool $lastOnly
     * @return ListMenu
     */
    public function setMenuPath(?string $menuPath, bool $lastOnly = false): ListMenu
    {
        if ($lastOnly) {
            $prefix = $this->getMenuPath(true);
            if (strlen($prefix) > 0) {
                $menuPath = '/' . $menuPath;
            }
            $this->setMenuPath($prefix . $menuPath);
        } else {
            $this->menuPath = $menuPath;
        }
        return $this;
    }

    /**
     * @return BackgroundColor|null
     */
    public function getSelectionColor(): ?BackgroundColor
    {
        return $this->selectionColor;
    }

    /**
     * @param BackgroundColor|null $selectionColor
     */
    public function setSelectionColor(?BackgroundColor $selectionColor): void
    {
        $this->selectionColor = $selectionColor;
    }

    /**
     *
     * @param string $part
     */
    public function appendMenuPath(string $part)
    {
        $prefix = $this->getMenuPath();
        if (strlen($prefix) > 0) {
            $part = '/' . $part;
        }
        $this->setMenuPath($prefix . $part);
    }

    /**
     * Iterate over structure and add all containers
     * @param Node $data
     */
    private function addContainers(Node $data)
    {
        if ($data->hasChildren()) {
            $this->add($data->getContainer());
            foreach ($data->getChildren() as $children) {
                $this->addContainers($children);
            }
        }
    }

    /**
     * Update rendering (container active flag, select option, update menu)
     */
    private function updateSelection()
    {
        Profiler::start();

        $menuItems = $this->structure->getData($this->getMenuPath(true));
        $container = $menuItems->getContainer();
        if ($container->getUUID() != $this->lastContainer->getUUID()) {
            $this->lastContainer->setActive(false);
            $container->setActive(true);
            $container->getParent()->setValid(false);
            $this->lastContainer = $container;
        }

        //deselect last selected component
        $this->deselect($this->lastSelected);
        //update selected element
        /** @var Component $selected */
        $selected = $this->structure->getData($this->getMenuPath())->getContent();
        //select current component
        $this->select($selected);
        $this->lastSelected = $selected;
        $this->hideMenuItems();
        Profiler::end();
    }


    /**
     * @param int $height
     */
    private function hideMenuItems(int $height = -1)
    {
        if ($height > 0) {
            $this->lastHeight = $height;
        }
        if ($this->lastHeight == -1) {
            return;
        }
        $menu = $this->structure->getData($this->getMenuPath(true));
        //if submenu exists for current menu (should always be true)
        //if overflow exists
        if ($menu->hasChildren() && $this->lastHeight < $menu->getChildrenCount()) {
            $this->updateItemWindow();
            $this->updateItemsVisibility();
        }
    }

    private function updateItemWindow()
    {
        $menu = $this->structure->getData($this->getMenuPath(true));
        $items = $menu->getChildren();
        $selectedItem = $this->getSelectedItem();
        //move item window
        $position = array_search($selectedItem, array_keys($items));
        //up movement
        if ($position - 1 < $this->itemWindowStart && $this->itemWindowStart > 0) {
            $this->itemWindowStart = $position - 1;
        }
        //down movement
        if ($position > ($this->itemWindowStart + $this->lastHeight) - 2) {
            if ($this->itemWindowStart + $this->lastHeight < $menu->getChildrenCount()) {
                $this->itemWindowStart++;
            }
        }
    }

    private function updateItemsVisibility()
    {
        $menu = $this->structure->getData($this->getMenuPath(true));
        //update items according to item window
        $i = 0;
        foreach ($menu->getChildren() as $item) {
            $flag = false;
            if ($i >= $this->itemWindowStart && $i < $this->itemWindowStart + $this->lastHeight) {
                $flag = true;
            }
            $item[self::CONTENT]->getParent()->setActive($flag);
            $i++;
        }
        $menu->getContainer()->setValid(false);
    }

    /**
     * Select previous option in current menu
     */
    private function selectPreviousOption()
    {
        $set = $this->structure->getData($this->getMenuPath(true))->getChildren();
        $this->setMenuPath($this->selectPreviousOptionFromSet($set, $this->getSelectedItem()), true);
        $this->updateSelection();
    }

    /**
     * Select next option in current menu
     */
    private function selectNextOption()
    {
        $set = array_reverse($this->structure->getData($this->getMenuPath(true))->getChildren());
        $this->setMenuPath($this->selectPreviousOptionFromSet($set, $this->getSelectedItem()), true);
        $this->updateSelection();
    }

    /**
     * Open parent menu
     */
    private function selectPreviousMenu()
    {
        if (strpos($this->getMenuPath(), '/') !== false) {
            $this->setMenuPath($this->getMenuPath(true));
            $this->updateSelection();
        }
    }

    /**
     * Open submenu
     */
    private function selectNextMenu()
    {
        $data = $this->structure->getData($this->getMenuPath());
        if ($data->hasChildren()) {
            $firstKey = array_key_first($data->getChildren());
            $this->appendMenuPath($firstKey);
        }
        $this->updateSelection();
    }

    /**
     * Match option passed as current and return previous one (or current if it's first)
     * @param array $set
     * @param $current
     * @return int|string|null
     */
    private function selectPreviousOptionFromSet(array $set, $current)
    {
        $previous = array_key_first($set);
        foreach ($set as $id => $item) {
            if ($id == $current) {
                break;
            }
            $previous = $id;
        }
        return $previous;
    }

    /**
     * Exec callable passed as 'handler'
     */
    private function execHandler()
    {
        $data = $this->structure->getData($this->getMenuPath());
        if ($data->hasHandler()) {
            $data->executeHandler($data);
        }
    }

    /**
     * Called when some element is deselected in item window
     * @param Component $component
     */
    protected function deselect(BaseComponent $component)
    {
        //clear background color -> parent will be transparent
        $component->getParent()->setBackgroundColor(null);
        $component->getParent()->setValid(false);
        $component->getParent()->setCustomFlag('selected', false);
        if ($component instanceof TextComponent) {
            //get previous text color
            $component->getText()->setTextColor($component->getCustomFlag('nativeTextColor'));
        }
    }

    /**
     * Called when element is selected in item window
     * @param Component $component
     */
    protected function select(BaseComponent $component)
    {
        if ($component instanceof TextComponent) {
            //save current text color
            $component->setCustomFlag('nativeTextColor', $component->getText()->getTextColor());
            $component->getText()->setTextColor($this->getBackgroundColor()->getTextColor());
        }
        $component->getParent()->setBackgroundColor($this->getSelectionColor());
        $component->getParent()->setValid(false);
        $component->getParent()->setCustomFlag('selected', true);
    }

    /**
     * Create container with divided layout for single menu
     * @param array $submenu
     * @return MenuPageContainer
     * @throws IncorrectProportionsException
     */
    public function createContainer(array $submenu): MenuPageContainer
    {
        $container = new MenuPageContainer();
        $container->setMenuObject($this);
        $layout = new LayoutVertical($this->generateDivisions(count($submenu)));
        $container->setLayout($layout);
        /** @var Node $data */
        foreach ($submenu as $data) {
            $subContainer = new Container();
            $subContainer->setLayout(new LayoutHorizontal(['*', '3px']));
            $pointer = (new TextComponent("→"))->setTextAlign(HorizontalAlign::MIDDLE());
            if (!$data->hasChildren()) {
                $pointer->setActive(false);
            }
            $subContainer->add($data->getContent(), $pointer);
            $container->add($subContainer);
        }
        $container->setActive(false);
        return $container;
    }

    /**
     * Divide container into pixels and free remaining space
     * @param int $count
     * @return array
     */
    private function generateDivisions(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = new Pixel(1);
        }
        $result[] = '*';
        return $result;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setShowBreadCrumbs(bool $flag): ListMenu
    {
        $this->getStructure()->setShowBreadcrumbs($flag);
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowBreadcrumbs(): bool
    {
        return $this->getStructure()->isShowBreadcrumbs();
    }


    protected function onKeyDown(KeyDownEvent $event): bool
    {
        if (!$this->structure->isValid()) {
            return false;
        }
        if ($event->isKey($this->keyBindings->getBinding(MenuKeyBindings::PREV_OPTION()))) {
            $this->selectPreviousOption();
        }
        if ($event->isKey($this->keyBindings->getBinding(MenuKeyBindings::NEXT_OPTION()))) {
            $this->selectNextOption();
        }
        if ($event->isKey($this->keyBindings->getBinding(MenuKeyBindings::PREV_SUBMENU()))) {
            $this->selectPreviousMenu();
        }
        if ($event->isKey($this->keyBindings->getBinding(MenuKeyBindings::NEXT_SUBMENU()))) {
            $this->selectNextMenu();
        }
        if ($event->isKey($this->keyBindings->getBinding(MenuKeyBindings::CONFIRM()))) {
            $this->execHandler();
        }
        return false;
    }

    public function _passEvent(KeyDownEvent $event): bool
    {
        if (!$this->isActive() || !$this->hasFocus()) {
            return false;
        }
        $container = $this->structure->getData($this->getMenuPath(true))->getContainer();
        if (!$container->_receiveEvent($event)) {
            return $container->_passEvent($event);
        }
        return true;
    }

    public function onParentSet($revalidation)
    {
        //run only when direct parent is set
        if (!$revalidation) {
            $menu = $this->structure->getData('');
            $firstId = array_key_first($menu->getChildren());
            $this->setMenuPath($firstId);
            $this->lastSelected = $menu->getChildren()[$firstId]->getContent();
            $this->select($this->lastSelected);
            $this->lastContainer = $menu->getContainer();
            $this->lastContainer->setActive(true);
        }
    }


}
