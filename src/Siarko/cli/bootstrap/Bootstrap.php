<?php


namespace Siarko\cli\bootstrap;


use Siarko\cli\bootstrap\events\EventData;
use Siarko\cli\bootstrap\events\EventProvider;
use Siarko\cli\bootstrap\events\Events;
use Siarko\cli\bootstrap\events\ExitEvent;
use Siarko\cli\bootstrap\events\InitEvent;
use Siarko\cli\bootstrap\exceptions\NoEventProvidersException;
use Siarko\cli\io\input\event\KeyCodes;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\input\Keyboard;
use Siarko\cli\io\Output;
use Siarko\cli\paradigm\Singleton;
use Siarko\cli\util\Vec;

class Bootstrap
{

    const DIRECTORY = __DIR__;
    const CURRENT_DIR = "src/Siarko/cli/bootstrap";
    static $ROOT_DIR = "";

    private $processes = [];
    private $eventProviders = [];
    private $run = true;
    private $cleanedUp = false;

    use Singleton;

    public function __construct()
    {
        pcntl_async_signals(true);

        register_shutdown_function(function () {
            $this->cleanup();
        });
        //catch terminate signal (ctrl-c)
        pcntl_signal(SIGTERM, function () {
            //Keyboard::get()->addOutsideEvent(new KeyDownEvent([KeyCodes::CTRL_C]));
            echo "SIG_TERM received\n";
        });
        //catch suspend signal (ctrl-z)
        pcntl_signal(SIGTSTP, function () {
            //Keyboard::get()->addOutsideEvent(new KeyDownEvent([KeyCodes::CTRL_Z]));
            echo "SIG_TSTP received\n";
        });
        pcntl_signal(SIGINT, function () {
            //$this->cleanup();
            //echo "SIG_INT received\n";
            Keyboard::get()->addOutsideEvent(new KeyDownEvent([KeyCodes::CTRL_C]));
        });
        pcntl_signal(SIGUSR1, function () {
            $this->cleanup();
            echo "SIG_USR1 received\n";
        });

        $this->setRootDir();
        $this->addEventProvider(InitEvent::get());
    }

    public function setProcess(\Closure $param, $eventType = Events::IDLE)
    {
        $this->processes[$eventType][] = $param;
    }

    public function addEventProvider(EventProvider $eventProvider)
    {
        $this->eventProviders[] = $eventProvider;
    }

    public function run()
    {
        if (count($this->eventProviders) == 0) {
            throw new NoEventProvidersException("No event providers registered");
        }
        try {
            while ($this->run) {
                /* @var $eventProvider EventProvider */
                foreach ($this->eventProviders as $eventProvider) {
                    $this->processEvent($eventProvider);
                }
            }
        } catch (\Exception $e) {
            $this->cleanup($e);
        }

    }

    private function processEvent(EventProvider $eventProvider)
    {
        $eventData = $eventProvider->getDataObject();
        if (
            !($eventData instanceof EventData) ||
            !array_key_exists($eventData->getState(), $this->processes)
        ) {
            return false;
        }
        $receivers = $this->processes[$eventData->getState()];
        foreach ($receivers as $process) {
            if (is_callable($process)) {
                call_user_func($process, ...$eventData->getProcessorArguments());
            }
        }

    }

    public function stop()
    {
        $this->run = false;
    }

    public static function getHomeDir()
    {
        $home = getenv('HOME');
        if (!empty($home)) {
            return rtrim($home, '/');
        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            return rtrim($home, '\\/');
        }
        return null;
    }

    private function setRootDir()
    {
        $end = strlen(Bootstrap::CURRENT_DIR);
        $len = strlen(self::DIRECTORY);
        Bootstrap::$ROOT_DIR = substr(self::DIRECTORY, 0, $len - $end);
    }

    public function cleanup($e = null)
    {
        if ($this->cleanedUp) {
            return;
        }
        $this->cleanedUp = true;
        $data = [];
        if ($this->run || $e) {
            /** @var \Exception $e */
            if ($e) {
                file_put_contents('last_error.log', $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
                $data['message'] = $e->getMessage();
                $data['trace'] = $e->getTraceAsString();
            } else {
                $data = error_get_last();
                if (is_array($data) && count($data)) {
                    $message = $data['message'] . "\nAt " . $data['file'] . "\nOn line " . $data['line'] . "\n";
                    file_put_contents('last_error.log', $message);
                }
            }
        }
        $this->processEvent(new ExitEvent([$data]));
    }
}