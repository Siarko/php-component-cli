<?php

namespace Siarko\cli\io;


use Siarko\cli\paradigm\Singleton;
use Siarko\cli\io\output\DefaultOutputBuffer;
use Siarko\cli\io\output\IOutBuffer;
use Siarko\cli\util\profiler\Profiler;
use Siarko\cli\util\Vec;

class Output
{
    use Singleton;

    public const MODE_CLEAR = 0;
    public const MODE_FLIP_BUFFER = 1;

    private ?Vec $screenSize = null;
    private $screenMode;

    private $outputBuffer;

    private function __construct($outputBuffer = null, $screenMode = self::MODE_CLEAR)
    {
        if(!($outputBuffer instanceof IOutBuffer)){
            $outputBuffer = new DefaultOutputBuffer();
        }

        $this->screenMode = $screenMode;
        $this->initScreen($screenMode);
        $this->outputBuffer = $outputBuffer;
    }

    public function print($content, $position = null){
        $this->outputBuffer->write($content, $position);
    }

    /**
     * @return IOutBuffer
     */
    public function getOutputBuffer(){
        return $this->outputBuffer;
    }

    public function applyBuffer(){
        if($this->outputBuffer->changed()){
            Profiler::start();
            $this->outputBuffer->apply();
            Profiler::end();
        }
    }

    /**
     * @return Vec
     */
    public function getScreenSize(): ?Vec
    {
        if($this->screenSize === null){
            $this->updateScreenSize();
        }
        return $this->screenSize;
    }

    /**
     * Invalidate screen size -> it will be regenerated next time it is requested
     */
    public function invalidateScreenSize(){
        $this->screenSize = null;
    }

    private function updateScreenSize(){
        Profiler::start();
        $this->screenSize = new Vec(
            exec('tput cols'),
            exec('tput lines')
        );
        Profiler::end();
    }

    private function initScreen($screenMode){
        if($screenMode == self::MODE_CLEAR){
            system('clear');
        }else{
            //switch to second console buffer
            system('tput smcup');
            //disable cursor
            system('tput civis');
        }
    }

    public function cleanup($errorData){
        $this->exitScreen();
        if(is_array($errorData) && count($errorData)){
            echo "Error occured, see last_error.log for details\n";
        }
        Profiler::end();
        Profiler::print();
    }

    private function exitScreen(){
        if($this->screenMode == self::MODE_FLIP_BUFFER) {
            //enable cursor
            system('tput cnorm');
            //switch to primary console buffer
            system('tput rmcup');
        }
    }

    /**
     * @return int
     */
    public function getScreenMode(): int
    {
        return $this->screenMode;
    }

}
