<?php

namespace Siarko\psw\views;
use Siarko\cli\io\input\event\KeyCodes;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\io\output\styles\TextColor;
use Siarko\cli\ui\components\base\border\lineBorder\LineBorder;
use Siarko\cli\ui\components\base\border\lineBorder\LineBorderVariant;
use Siarko\cli\ui\components\base\ComponentSizing;
use Siarko\cli\ui\components\Container;
use Siarko\cli\ui\components\menu\listMenu\ListMenu;
use Siarko\cli\ui\components\menu\listMenu\structure\Node;
use Siarko\cli\ui\components\menu\listMenu\structure\RootNode;
use Siarko\cli\ui\components\Modal;
use Siarko\cli\ui\components\TextComponent;
use Siarko\cli\ui\components\View;
use Siarko\cli\ui\FocusController;
use Siarko\cli\ui\layouts\LayoutFill;
use Siarko\cli\ui\layouts\LayoutHorizontal;
use Siarko\cli\util\unit\Percent;
use Siarko\cli\util\unit\Pixel;
use Siarko\psw\os\project\Project;

class MainView extends View
{

    public function __construct()
    {

        $focusController = new FocusController();

        $mainContainer = new Container();
        $mainContainer->setLayout(new LayoutFill());
        $mainContainer->setParent($this);
        $mainContainer->setBackgroundColor(BackgroundColor::BLACK());
        $mainContainer->setBorder(
            (new LineBorder())
                ->setColor(TextColor::LIGHT_GRAY())
                ->setTitle("Project Tools")
        );


        $projects = Project::find();

        $projectManagementModal = new Modal();
        $projectManagementModal->add(
            (new Container())->setName('content')
                ->add((new TextComponent('Modal'))->setName('text'))
            ->setBackgroundColor(BackgroundColor::BLACK())
            ->setSizing(
                (new ComponentSizing())
                ->setMaxSize(new Percent(70), new Percent(70))
                ->setBorder(new LineBorder(LineBorderVariant::DOUBLE_LINE()))
            )
        );
        $projectManagementModal->addKeyDownHandler(function(KeyDownEvent $data) use ($projectManagementModal, $focusController){
            if($data->isKey(KeyCodes::ARROW_LEFT)){
                $projectManagementModal->setVisible(false);
                $focusController->back();
            }
        });

        $menuList = new RootNode();
        /** @var Project $project */
        foreach ($projects as $project) {
            $projectOption = new Node();
            $projectOptionContainer = new Container();
            $projectOptionContainer->setLayout(new LayoutHorizontal([new Pixel(2), new Pixel(1), '*']));
            $projectStateIndicator = new TextComponent('  ');
            if($project->isAnyRunning()){
                if($project->isFullyRunning()){
                    $projectStateIndicator->setBackgroundColor(BackgroundColor::LIGHT_GREEN());
                }else{
                    $projectStateIndicator->setBackgroundColor(BackgroundColor::LIGHT_YELLOW());
                }
            }else{
                $projectStateIndicator->setBackgroundColor(BackgroundColor::LIGHT_RED());
            }
            $projectOptionContainer->add($projectStateIndicator);
            $projectOptionContainer->add(new TextComponent());
            $projectOptionContainer->add(new TextComponent($project->getName()));
            $projectOption->setContent($projectOptionContainer);
            $projectOption->setTitle($project->getName());
            foreach ($project->getContainers() as $container) {
                $containerOption = new Node();
                $containerContent = new Container();
                $containerContent->setLayout(new LayoutHorizontal([new Pixel(2), new Pixel(1), '*']));
                $stateIndicator = new TextComponent('  ');
                $stateIndicator->setBackgroundColor(
                    (!$container->isRunning()) ?
                        BackgroundColor::LIGHT_RED() : BackgroundColor::LIGHT_GREEN()
                );
                $containerContent->add($stateIndicator);
                $containerContent->add(new Container());
                $containerContent->add(new TextComponent($container->getName()));
                $containerOption->setContent($containerContent);
                $projectOption->addOption(trim($container->getFullName(), '/'), $containerOption);
            }

            $projectOption->setHandler(function(Node $data) use ($project, $projectManagementModal, $focusController) {
                /** @var Container $textComponent */
                $container = $projectManagementModal->findChildren('content', true);
                $container->getSizing()->getBorder()->setTitle($project->getName());
                //$textComponent->getStyledText()->setText($project->getName());

                $projectManagementModal->setVisible(true);
                $focusController->focus($projectManagementModal);
            });
            $menuList->addOption($project->getName(), $projectOption);
        }

        $menu = new ListMenu($menuList);
        $menu->setShowBreadCrumbs(true);
        $menu->setSelectionColor(BackgroundColor::LIGHT_GRAY());
        $focusController->focus($menu);

        $menuC = new Container();
        $menuC->setBackgroundColor(BackgroundColor::DARK_GRAY());
        $menuC->add($menu);

        $mainContainer->add($menuC);
        $mainContainer->add($projectManagementModal);
        $mainContainer->setPermanentFocus(true);
    }

}