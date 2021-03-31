<?php


namespace Siarko\cli\ui\components\base;


use Siarko\cli\bootstrap\exceptions\ParamTypeException;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\Output;
use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\ui\components\ComponentFilter;
use Siarko\cli\ui\components\Modal;
use Siarko\cli\ui\components\View;
use Siarko\cli\ui\layouts\AbstractLayout;
use Siarko\cli\ui\layouts\Layout;
use Siarko\cli\ui\layouts\LayoutFill;
use Siarko\cli\util\BoundingBox;
use Siarko\cli\util\Cacheable;
use Siarko\cli\util\graphics\Shape;
use Siarko\cli\util\HandlerQueue;
use Siarko\cli\util\profiler\Profiler;

abstract class BaseComponent implements Drawable
{

    use Cacheable;

    private const CACHE_BB = 'bb';
    private static $UUID_INDEX = 0;

    //UUID unique identifier
    private int $UUID;

    private ?BackgroundColor $backgroundColor = null;
    //If component is focused, it will receive keyboard input
    private bool $focused = false;
    //if it's permanently focused, it will always receive input
    private bool $permanentFocus = false;
    // Should element and it's children be drawn
    private bool $visible = true;
    // Should element receive events and and be taken into account while caclulating layouts
    private bool $active = true;
    // is component floating - floating components are not calculated in layouts as children
    private bool $floating = false;
    //layer id - for drawing in layers. Lower layer id's are drawn first
    protected int $layer = 0;
    //if invalid - should be redrawn
    protected bool $valid = false;

    //custom flags for passing values via components
    private array $customFlags = [];

    private HandlerQueue $focusChangeHandlers;

    /**
     * Sizing parameters of component (margin/padding/border)
     * @var ComponentSizing
     */
    private ComponentSizing $sizing;
    /**
     * @var BaseComponent | View
     */
    private $parent;
    private AbstractLayout $layout;
    /**
     * @var BaseComponent[]
     */
    private array $children = [];

    /**
     * BaseComponent constructor.
     */
    public function __construct()
    {
        $this->setUUID(static::createUUID());
        $this->setLayout(new LayoutFill());
        $this->setSizing(new ComponentSizing());
        $this->focusChangeHandlers = new HandlerQueue();
    }

    /**
     * Draws component's border and background
     * Calls drawContent
     */
    public function draw()
    {
        if (!$this->isVisible()) {
            return;
        }

        if (!$this->isValid()) {
            Profiler::start('background/border');
            $bounds = $this->getBB();
            $this->setCache(self::CACHE_BB, $bounds);
            $x = $bounds->getX();
            $y = $bounds->getY();
            $out = Output::get()->getOutputBuffer();


            if ($this->getBackgroundColor()->getValue() != BackgroundColor::TRANSPARENT) {
                Profiler::start("background");
                //get line filled with spaces
                $bgRow = $this->getSizing()->getBorder()->getMiddleBorderRow($this, $bounds->getWidth());
                Shape::rectangle($bounds, $bgRow, $this->getLayer());
                Profiler::end();
            }

            Profiler::start('borders');
            //draw top border row
            $out->setCursorPosition($x, $y);
            $out->writeToLayer($this->getSizing()->getBorder()->getTopBorderRow($this, $bounds->getWidth()),
                $this->getLayer());
            //draw bottom border row
            $out->setCursorPosition($x, $y + $bounds->getHeight() - 1);
            $out->writeToLayer($this->getSizing()->getBorder()->getBottomBorderRow($this, $bounds->getWidth()),
                $this->getLayer());
            Profiler::end();
            Profiler::end();
        }

        $this->updateContent( true);
        $this->setValid(true);
    }

    /**
     * Draw content, optionally with child elements
     * @param bool $updateChildren
     */
    protected function updateContent(bool $updateChildren = false)
    {
        if (!$this->isValid()) {
            Profiler::start('self-content');
            $bounds = $this->getCache(self::CACHE_BB, function(){
                return $this->getBB();
            });
            $contentBB = $bounds->clone();
            $border = $this->getSizing()->getBorder();
            $contentBB->setX($contentBB->getX() + $border->collapseLeft());
            $contentBB->setY($contentBB->getY() + $border->collapseTop());
            $contentBB->setX1($contentBB->getX1() - $border->collapseRight() - $border->collapseLeft());
            $contentBB->setY1($contentBB->getY1() - $border->collapseBottom() - $border->collapseTop());
            $this->drawContent($contentBB);
            Profiler::end();
        }

        if ($updateChildren) {
            $this->drawChildren();
        }
    }

    /**
     * Method for drawing content of a component, called by draw
     * @param BoundingBox $contentBox
     * @return mixed
     */
    public abstract function drawContent(BoundingBox $contentBox);

    public function getBB()
    {
        if ($this->parent instanceof View) {
            return $this->getScreenBB();
        }
        if ($this->parent instanceof BaseComponent) {
            return $this->getBBAsChild();
        }
        return new BoundingBox(1, 1, 0, 0);
    }

    protected function getScreenBB(): BoundingBox
    {
        Profiler::start();
        $size = Output::get()->getScreenSize();
        $x = 1 + $this->getSizing()->getMargin()->getLeft();
        $y = 1 + $this->getSizing()->getMargin()->getUp();
        $w = 1 + $size->getX() - $x - $this->getSizing()->getMargin()->getRight();
        $h = 1 + $size->getY() - $y - $this->getSizing()->getMargin()->getDown();
        Profiler::end();
        return new BoundingBox($x, $y, $w, $h);
    }


    protected function getBBAsChild(): BoundingBox
    {
        Profiler::start();
        $parentBB = $this->parent->getLayout()->getBB($this);
        $value = new BoundingBox(
            $parentBB->getX() + $this->getSizing()->getMargin()->getLeft(),
            $parentBB->getY() + $this->getSizing()->getMargin()->getUp(),
            $parentBB->getX1() - $this->getSizing()->getMargin()->getRight(),
            $parentBB->getY1() - $this->getSizing()->getMargin()->getDown(),
            true
        );
        Profiler::end();
        return $value;
    }

    /**
     * If true, component will receive keyboard events permanently
     * @param $flag
     */
    public function setPermanentFocus($flag)
    {
        $this->permanentFocus = $flag;
    }

    /**
     * @return bool
     */
    public function hasPermanentFocus(): bool
    {
        return $this->permanentFocus;
    }

    /**
     * @return bool
     */
    public function hasFocus(): bool
    {
        return $this->focused;
    }

    /**
     * @param bool $flag
     * @return BaseComponent
     */
    public function setFocus(bool $flag): BaseComponent
    {
        $this->focused = $flag;
        $this->focusChangeHandlers->execute($flag);
        return $this;
    }

    /**
     * Focus on this component - it will receive keyboard events
     */
    public function focus(): BaseComponent
    {
        return $this->setFocus(true);
    }

    /**
     * Unfocus this component - it will not receive keyboard events
     */
    public function unFocus(): BaseComponent
    {
        return $this->setFocus(false);
    }

    /**
     * Adds handlers executed on focus state change
     * Passes bool new state of focus
     * @param callable $handler
     * @return $this
     */
    public function onFocusChange(callable $handler): BaseComponent
    {
        $this->focusChangeHandlers->add($handler);
        return $this;
    }

    /**
     * @param BaseComponent|View $parent
     * @return $this
     */
    public function setParent(&$parent): BaseComponent
    {
        $this->parent = &$parent;
        if ($parent instanceof View) {
            $parent->setMainComponent($this);
            $this->setLayer(0);
        } else {
            if (!($this instanceof Modal)) {
                $this->setLayer($parent->getLayer() + 1);
            }
        }
        $this->revalidateChildrenParent();
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return BackgroundColor
     */
    public function getBackgroundColor(): ?BackgroundColor
    {
        if (is_null($this->backgroundColor)) {
            if ($this->parent instanceof Component) {
                return $this->parent->getBackgroundColor();
            } else {
                return null;
            }
        }
        return $this->backgroundColor;
    }

    /**
     * @param ?BackgroundColor $backgroundColor
     * @return BaseComponent
     */
    public function setBackgroundColor(?BackgroundColor $backgroundColor): BaseComponent
    {
        $this->backgroundColor = $backgroundColor;
        return $this;
    }

    /**
     * @param AbstractLayout $layout
     * @return $this
     */
    public function setLayout(AbstractLayout $layout): BaseComponent
    {
        $this->layout = $layout;
        $this->layout->setParent($this);
        return $this;
    }

    /**
     * @return AbstractLayout
     */
    public function getLayout(): AbstractLayout
    {
        return $this->layout;
    }

    /**
     * Draws children of this component
     */
    protected function drawChildren()
    {
        Profiler::start();
        $this->getLayout()->drawChildren(
            $this,
            $this->getChildren(ComponentFilter::active())
        );
        Profiler::end();
    }

    /**
     * @param mixed ...$filters
     * @return BaseComponent[]
     */
    public function getChildren(callable ...$filters): array
    {
        if (count($filters) == 0) {
            return $this->children;
        } else {
            $result = $this->children;
            foreach ($filters as $filter) {
                $result = array_filter($result, $filter);
            }
            return $result;
        }
    }

    /**
     * Add new child component
     * @param BaseComponent ...$components
     * @return $this
     */
    public function add(BaseComponent ...$components): BaseComponent
    {
        foreach ($components as $component) {
            $this->children[] = $component;
            $component->setParent($this);
        }
        return $this;
    }

    /**
     * Remove child components
     * @param BaseComponent ...$components
     * @return $this
     */
    public function abortChild(BaseComponent ...$components): BaseComponent
    {
        foreach ($components as $component) {
            $key = array_search($component, $this->children);
            if ($key !== false) {
                unset($this->children, $key);
            }
        }
        return $this;
    }

    /**
     * hehe
     */
    public function abortAllChildren()
    {
        $this->children = [];
    }

    /* SIZING => PADDING / MARGIN / BORDER */

    /**
     * @return ComponentSizing
     */
    public function getSizing(): ComponentSizing
    {
        return $this->sizing;
    }

    /**
     * @param ComponentSizing $sizing
     * @return BaseComponent
     */
    public function setSizing(ComponentSizing $sizing): BaseComponent
    {
        $this->sizing = $sizing;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     * @return BaseComponent
     */
    public function setVisible(bool $visible): BaseComponent
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return BaseComponent
     */
    public function setActive(bool $active): BaseComponent
    {
        if ($this->parent && $this->active != $active) {
            $this->parent->getLayout()->setValid(false);
        }
        $this->active = $active;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFloating(): bool
    {
        return $this->floating;
    }

    /**
     * @param bool $floating
     * @return BaseComponent
     */
    public function setFloating(bool $floating): BaseComponent
    {
        $this->floating = $floating;
        return $this;
    }

    /**
     * @return int
     */
    public function getLayer(): int
    {
        return $this->layer;
    }

    /**
     * @param int $layer
     * @return BaseComponent
     */
    public function setLayer(int $layer): BaseComponent
    {
        $this->layer = $layer;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     * @return BaseComponent
     */
    public function setValid(bool $valid): BaseComponent
    {
        //invalidate children but don't do it over and over again
        if(!$valid && $this->valid){
            $this->getLayout()->setValid(false);
            $this->cachePurge();
            $this->invalidateChildren(true);
        }

        $this->valid = $valid;
        return $this;
    }

    /**
     * @return int
     */
    public function getUUID(): int
    {
        return $this->UUID;
    }

    /**
     * @param int $UUID
     */
    public function setUUID(int $UUID): void
    {
        $this->UUID = $UUID;
    }

    protected function getComponentDebugPath()
    {
        if ($this->parent instanceof BaseComponent) {
            $parentPath = $this->parent->getComponentDebugPath();
        } else {
            if (!is_null($this->parent)) {
                $parentPath = $this->getClassName($this->parent);
            } else {
                $parentPath = 'NULL';
            }
        }
        return $parentPath . '/' . $this->getClassName($this);
    }

    protected function getClassName(object $object)
    {
        $name = get_class($object);
        $e = explode('\\', $name);
        return $e[array_key_last($e)];
    }

    private function revalidateChildrenParent()
    {
        Profiler::start();
        foreach ($this->getChildren() as $child) {
            $child->setParent($this);
        }
        Profiler::end();
    }

    /**
     * Set custom flag - it can have any value and any key
     * Can be used for passing custom values and marking components
     * @param $id
     * @param $value
     */
    public function setCustomFlag($id, $value){
        $this->customFlags[$id] = $value;
    }

    /**
     * @param $id
     * @param null $default
     * @return mixed|null
     */
    public function getCustomFlag($id, $default = null){
        if(array_key_exists($id, $this->customFlags)){
            return $this->customFlags[$id];
        }else{
            return $default;
        }
    }


    /**
     * Get new UUID
     * @return int
     */
    protected static function createUUID(): int
    {
        return ++static::$UUID_INDEX;
    }

    public static function getContainerCount(): int
    {
        return static::$UUID_INDEX;
    }

    private function invalidateChildren($deep = false)
    {
        foreach ($this->getChildren() as $child) {
            $child->setValid(false);
            if($deep){
                $child->invalidateChildren($deep);
            }

        }
    }
}