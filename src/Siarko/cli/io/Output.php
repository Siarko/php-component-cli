<?php

namespace Siarko\cli\io;


use Siarko\cli\io\output\termCapabilities\ColorCapability;
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
            $this->clearScreen();
        }else{
            $this->setSecondaryBuffer();
            $this->disableCursor();
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
            $this->setPrimaryBuffer();
            $this->enableCursor();
        }
    }

    /**
     * @return int
     */
    public function getScreenMode(): int
    {
        return $this->screenMode;
    }

    public function setPrimaryBuffer(){
        system('tput rmcup');
    }
    public function setSecondaryBuffer(){
        system('tput smcup');
    }
    public function enableCursor(){
        //show cursor
        system('tput cnorm');
        //echo input characters to console
        system('stty echo');
    }
    public function disableCursor(){
        system('tput civis');
        //cbreak enabled for special term controll characters and -echo for hiding input characters
        system('stty cbreak -echo');
    }
    public function clearScreen(){
        system('clear');
    }

    /**
     * Check if terminal can handle colors; if yes -> how many
     * @return ColorCapability
     */
    public function getColorCapability(): ColorCapability
    {
        return new ColorCapability(system('tput colors'));
    }

}
