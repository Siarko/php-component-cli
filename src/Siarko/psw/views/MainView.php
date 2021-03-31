<?php

namespace Siarko\psw\views;
use Siarko\cli\io\input\event\KeyCodes;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\io\output\styles\TextColor;
use Siarko\cli\ui\components\base\border\lineBorder\LineBorder;
use Siarko\cli\ui\components\base\border\lineBorder\LineBorderVariant;
use Siarko\cli\ui\components\base\border\Margin;
use Siarko\cli\ui\components\Checkbox;
use Siarko\cli\ui\components\Container;
use Siarko\cli\ui\components\InputLine;
use Siarko\cli\ui\components\menu\listMenu\ListMenu;
use Siarko\cli\ui\components\Modal;
use Siarko\cli\ui\components\TextComponent;
use Siarko\cli\ui\components\View;
use Siarko\cli\ui\layouts\align\HorizontalAlign;
use Siarko\cli\ui\layouts\align\VerticalAlign;
use Siarko\cli\ui\layouts\LayoutFill;
use Siarko\cli\ui\layouts\LayoutHorizontal;
use Siarko\cli\ui\layouts\LayoutVertical;
use Siarko\cli\ui\UIController;
use Siarko\cli\util\unit\Percent;
use Siarko\cli\util\unit\Pixel;

class MainView extends View
{


    public function __construct()
    {
        $mainContainer = new Container();
        $mainContainer->setLayout(new LayoutHorizontal(['20%', '*']));
        $mainContainer->setParent($this);
        $mainContainer->setBackgroundColor(new BackgroundColor(BackgroundColor::LIGHT_GREEN));
        $mainContainer->setBorder(
            (new LineBorder())
                ->setColor(new TextColor(TextColor::BLACK))
                ->setTitle("Main component")
        );



        $menu = new ListMenu([
            'item1' => [
                ListMenu::CONTENT => (new Container())->setLayout(new LayoutHorizontal(['*', new Pixel(2)]))->add(
                    (new TextComponent("Item 1")),
                    (new Checkbox())->focus()
                )
            ],
            'item2' => (new Container())->setLayout(new LayoutHorizontal(['*',new Pixel(10), new Pixel(2)]))->add(
                (new TextComponent("Item 2")),
                (new InputLine())->focus(),
                (new Checkbox())->focus()
            ),
            'item3' => [
                ListMenu::CONTENT => (new Container())->setLayout(new LayoutHorizontal(['*', new Pixel(2)]))->add(
                    (new TextComponent("Item 3")),
                    (new Checkbox())->focus()
                ),
                ListMenu::SUBMENU => [
                    'item3.1' => "Item 3.1",
                    'item3.2' => new TextComponent("Item 3.2"),
                    'item3.3' => [
                        ListMenu::SUBMENU => [
                            'item3.3.1' => "Item 3.3.1"
                        ]
                    ],
                    'item3.4' => [
                        ListMenu::SUBMENU => [
                            'item3.4.1' => "Item 3.4.1"
                        ]
                    ]
                ]
            ],
            'item4' => "Item 4",
            'item5' => "Item 5",
            'item6' => "Item 6",
            'item7' => [
                ListMenu::CONTENT => "Item 7",
                ListMenu::SUBMENU => [
                    "item7.1" => "Item 7.1"
                ]
            ],
            'item8' => "Item 8",
        ]);

        $menu->onFocusChange(function($newState) use ($menu){
            $menu->setBackgroundColor(new BackgroundColor(($newState) ? BackgroundColor::LIGHT_PURPLE : BackgroundColor::PURPLE));
            $menu->setValid(false);
        })->focus();
        $menu->getSizing()->setMaxSize(null, new Pixel(8));
        $menu->setShowBreadCrumbs(false);

        $menuC = new Container();
        $menuC->setBackgroundColor(new BackgroundColor(BackgroundColor::GREEN));
        $menuC->add($menu);

        $menu2 = new ListMenu([
            'item1' => [
                ListMenu::CONTENT => (new Container())->setLayout(new LayoutHorizontal(['*', new Pixel(2)]))->add(
                    (new TextComponent("Item 1")),
                    (new Checkbox())->focus()
                )
            ],
            'item2' => (new Container())->setLayout(new LayoutHorizontal(['*',new Pixel(10), new Pixel(2)]))->add(
                (new TextComponent("Item 2")),
                (new InputLine())->focus(),
                (new Checkbox())->focus()
            ),
            'item3' => [
                ListMenu::CONTENT => (new Container())->setLayout(new LayoutHorizontal(['*', new Pixel(2)]))->add(
                    (new TextComponent("Item 3")),
                    (new Checkbox())->focus()
                ),
                ListMenu::SUBMENU => [
                    'item3.1' => "Item 3.1",
                    'item3.2' => new TextComponent("Item 3.2"),
                    'item3.3' => [
                        ListMenu::SUBMENU => [
                            'item3.3.1' => "Item 3.3.1"
                        ]
                    ],
                    'item3.4' => [
                        ListMenu::SUBMENU => [
                            'item3.4.1' => "Item 3.4.1"
                        ]
                    ]
                ]
            ],
            'item4' => "Item 4",
            'item5' => "Item 5",
            'item6' => "Item 6",
            'item7' => [
                ListMenu::CONTENT => "Item 7",
                ListMenu::SUBMENU => [
                    "item7.1" => "Item 7.1"
                ]
            ],
            'item8' => "Item 8",
        ]);
        $menu2->onFocusChange(function($newState) use ($menu2){
            $menu2->setBackgroundColor(new BackgroundColor(($newState) ? BackgroundColor::LIGHT_PURPLE : BackgroundColor::PURPLE));
            $menu2->setValid(false);
        });

        $c = new Container();
        $c->add($menu2);
        $mainContainer->add($menuC, $c);

        $mainContainer->setPermanentFocus(true);
        $mainContainer->addKeyDownHandler(function(KeyDownEvent $event) use ($menu, $menu2){
            if($event->isKey(KeyCodes::TAB)){
                $menu->setFocus(!$menu->hasFocus());
                $menu2->setFocus(!$menu2->hasFocus());
            }
        });
    }

}