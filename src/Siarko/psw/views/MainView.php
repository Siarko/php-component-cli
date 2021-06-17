<?php

namespace Siarko\psw\views;
use Siarko\cli\bootstrap\Bootstrap;
use Siarko\cli\io\input\event\KeyCodes;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\input\Keyboard;
use Siarko\cli\io\Output;
use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\io\output\styles\Color;
use Siarko\cli\io\output\styles\TextColor;
use Siarko\cli\ui\components\base\border\lineBorder\LineBorder;
use Siarko\cli\ui\components\Checkbox;
use Siarko\cli\ui\components\Container;
use Siarko\cli\ui\components\InputLine;
use Siarko\cli\ui\components\menu\listMenu\ListMenu;
use Siarko\cli\ui\components\TextComponent;
use Siarko\cli\ui\components\View;
use Siarko\cli\ui\layouts\LayoutFill;
use Siarko\cli\ui\layouts\LayoutHorizontal;
use Siarko\cli\ui\UIController;
use Siarko\cli\util\os\Path;
use Siarko\cli\util\unit\Pixel;
use Siarko\psw\os\docker\Docker;
use Siarko\psw\os\project\Project;

class MainView extends View
{

    public function __construct()
    {

        $mainContainer = new Container();
        $mainContainer->setLayout(new LayoutFill());
        $mainContainer->setParent($this);
        $mainContainer->setBackgroundColor(BackgroundColor::BLACK());
        $mainContainer->setBorder(
            (new LineBorder())
                ->setColor(TextColor::LIGHT_GRAY())
                ->setTitle("Project Tools")
        );


        $projects = Project::find(new Path(Bootstrap::getHomeDir().'/Projects'));
        $menuList = [];
        /** @var Project $project */
        foreach ($projects as $project) {
            $menuList[$project->getDirname()] = [
                ListMenu::CONTENT => $project->getDirname(),
                ListMenu::SUBMENU => [
                    'exec' => [
                        ListMenu::CONTENT => 'Local docker exec',
                        ListMenu::HANDLER => function($data) use ($project){
                            UIController::get()->pauseApp();
                            Docker::ssh($project);
                            UIController::get()->resumeApp();
                        }
                    ],
                    'cloud' => [
                        ListMenu::CONTENT => 'Cloud tools',
                        ListMenu::SUBMENU => [
                            'stage' => 'Stage'
                        ],
                        ListMenu::DISABLED => true
                    ],
                    'options' => [
                        ListMenu::CONTENT => 'Options',
                        ListMenu::SUBMENU => [
                            'rename' => 'Rename',
                        ]
                    ]
                ]
            ];
        }

        $menu = new ListMenu($menuList);
        $menu->focus();
        $menu->setShowBreadCrumbs(true);
        $menu->setSelectionColor(BackgroundColor::LIGHT_GRAY());

        $menuC = new Container();
        $menuC->setBackgroundColor(BackgroundColor::DARK_GRAY());
        $menuC->add($menu);

        $mainContainer->add($menuC);
        $mainContainer->setPermanentFocus(true);
    }

}