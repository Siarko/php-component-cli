<?php


namespace Siarko\cli\ui\components\menu\listMenu;


use Siarko\cli\io\input\event\KeyCodes;
use Siarko\cli\util\KeyBindings;

/**
 * @method static MenuKeyBindings NEXT_OPTION()
 * @method static MenuKeyBindings PREV_OPTION()
 * @method static MenuKeyBindings NEXT_SUBMENU()
 * @method static MenuKeyBindings PREV_SUBMENU()
 * @method static MenuKeyBindings CONFIRM()
 * */
class MenuKeyBindings extends KeyBindings
{
    private const NEXT_OPTION = 0;
    private const PREV_OPTION = 1;
    private const NEXT_SUBMENU = 2;
    private const PREV_SUBMENU = 3;
    private const CONFIRM = 4;

    protected function init()
    {
        $this->setBinding(self::NEXT_OPTION(), KeyCodes::ARROW_DOWN);
        $this->setBinding(self::PREV_OPTION(), KeyCodes::ARROW_UP);
        $this->setBinding(self::NEXT_SUBMENU(), KeyCodes::ARROW_RIGHT);
        $this->setBinding(self::PREV_SUBMENU(), KeyCodes::ARROW_LEFT);
        $this->setBinding(self::CONFIRM(), KeyCodes::ENTER);
    }
}