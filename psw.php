<?php
declare(ticks = 1);

use Siarko\cli\bootstrap\Bootstrap;
use Siarko\cli\bootstrap\events\Events;
use Siarko\cli\io\input\event\KeyCodes;
use Siarko\cli\io\input\event\BootstrapKeyEvents;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\Output;
use Siarko\cli\io\input\Keyboard;
use Siarko\cli\ui\UIController;
use Siarko\cli\util\profiler\Profiler;
use Siarko\psw\config\MainConfig;
use Siarko\psw\views\MainView;

if(!file_exists('vendor')){
    echo "No vendor directory detected! Run 'composer install'.";
    exit(1);
}

require_once "vendor/autoload.php";

Profiler::setDefaultTimeFactor(\Siarko\cli\util\profiler\TimeFactor::MILLISECONDS());
Profiler::start("complete_run", \Siarko\cli\util\profiler\TimeFactor::SECONDS());
Profiler::setEnabled(false);

$bootstrap = Bootstrap::get();
$bootstrap->setProcess(function(){
    Profiler::start('Init_Process');
    MainConfig::get();
    Output::get([
        'screenMode' => Output::MODE_FLIP_BUFFER
    ]);
    UIController::get()->addView(new MainView());
    Profiler::end();
}, Events::INIT);

$bootstrap->setProcess(function() {
    UIController::get()->draw();
    Output::get()->applyBuffer();
}, Events::IDLE);

$bootstrap->setProcess(function(KeyDownEvent $event) use ($bootstrap) {
    Profiler::start('KeyDownEvent_main');
    if($event->isKey(KeyCodes::ESC)) {
        $bootstrap->stop();
    }
    if($event->isKey(KeyCodes::F5)){
        Output::get()->invalidateScreenSize();
        UIController::get()->update();
    }
    Profiler::end();
}, BootstrapKeyEvents::KEYUP);

$bootstrap->setProcess(function ($error){
    Output::get()->cleanup($error);
}, Events::EXIT);

$bootstrap->addEventProvider(Keyboard::get());

$bootstrap->run();