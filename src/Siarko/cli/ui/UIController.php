<?php


namespace Siarko\cli\ui;


use Siarko\cli\bootstrap\Bootstrap;
use Siarko\cli\bootstrap\exceptions\ParamTypeException;
use Siarko\cli\io\input\event\BootstrapKeyEvents;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\input\Keyboard;
use Siarko\cli\io\Output;
use Siarko\cli\paradigm\Singleton;
use Siarko\cli\ui\components\View;

class UIController
{
    use Singleton;

    private array $views = [];
    private int $activeViewId = 0;

    /**
     * UIController constructor.
     * @throws ParamTypeException
     */
    private function __construct()
    {
        Bootstrap::get()->setProcess(function(KeyDownEvent $event){
            $this->dispatchKeyboardEvent($event);
        }, BootstrapKeyEvents::KEYUP);
    }

    /**
     * @param KeyDownEvent $eventData
     * @throws exceptions\NoMainComponentException
     */
    private function dispatchKeyboardEvent(KeyDownEvent $eventData){
        $activeView = $this->getActiveView();
        if($activeView){
            $activeView->receiveKeyboardEvent($eventData);
        }
    }

    /**
     * Calls main draw wrapper
     */
    public function draw(){
        $activeView = $this->getActiveView();
        if($activeView){
            $activeView->draw();
        }
    }

    /**
     * invalidates active view and redraws
     */
    public function update(){
        $activeView = $this->getActiveView();
        if($activeView){
            $activeView->update();
        }
    }

    /**
     * Add new View
     * @param View $view
     * @return int id of view
     */
    public function addView(View $view): int
    {
        $id = count($this->views);
        $this->views[$id] = $view;
        $view->setId($id);
        return $id;
    }

    /**
     * Set actively used view
     * @param $view View|int
     */
    public function setActiveView($view){
        if($view instanceof View){
            $view = $view->getId();
        }
        $this->activeViewId = $view;
    }

    /**
     * @return View|null
     */
    private function getActiveView(){
        if(count($this->views) > 0){
            $view = $this->views[$this->activeViewId];
            if($view instanceof View){
                return $view;
            }
            return null;
        }
        return null;
    }

    /**
     * Useful for calling passthru with a command that takes input and throws stuff to stdout
     * It switches to primary screen, recovers default cursor state and pauses keyboard "hook"
     * @throws ParamTypeException
     */
    public function pauseApp(){
        Output::get()->setPrimaryBuffer();
        Output::get()->enableCursor();
        Keyboard::get()->pauseStream();
    }

    /**
     * Call this after PauseApp to restore app to working state
     */
    public function resumeApp(){
        Output::get()->setSecondaryBuffer();
        Output::get()->disableCursor();
        Keyboard::get()->catchStream();
        UIController::get()->update();
    }
}