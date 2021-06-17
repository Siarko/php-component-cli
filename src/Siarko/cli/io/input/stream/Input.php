<?php


namespace Siarko\cli\io\input\stream;


use Exception;
use Siarko\cli\bootstrap\Bootstrap;
use Siarko\cli\io\Output;

class Input
{

    private $stream = null;

    /* @var $buffer mixed */
    private $buffer = false;
    private bool $isReleased = false;

    public function __construct()
    {
        $this->catchStream();
    }

    public function catchStream()
    {
        if (!$this->isStreamWorking(STDIN)) {
            return false;
        }
        $this->isReleased = false;
        stream_set_blocking(STDIN, 0);
        //fopen(STDIN, 'r');
        $this->stream = STDIN;
        return true;
    }

    public function releaseStream(){
        stream_set_blocking(STDIN, 1);
        $this->isReleased = true;
    }

    public function isBufferEmpty()
    {
        if($this->isReleased){
            return true;
        }
        //buffer may contain some data from previous read
        if ($this->buffer) {
            return false;
        }
        $this->readString();
        return ($this->buffer === false);
    }

    public function setBufferOverflow($value)
    {
        $this->buffer = [$value];
    }

    public function getBufferContent()
    {
        $c = $this->buffer;
        $this->buffer = false;
        return $c;
    }

    /**
     * @throws Exception
     */
    private function readString()
    {
        if (!$this->isStreamWorking($this->stream) && !$this->catchStream()) {
            Bootstrap::get()->stop();
            Bootstrap::get()->cleanup();
            return;
        }
        if(feof($this->stream)){
            return;
        }
        $read = array($this->stream);
        $write = array();
        $except = array();
        $result = @stream_select($read, $write, $except, 0);
        /*
         * $result == 0 -> No input
         * $result == false -> selection fail (maybe interrupt)
         * */
        if ($result === 0 || $result === false) {
            $this->buffer = false;
            return;
        }
        stream_set_blocking($this->stream, 0);
        $bytes = [];
        do {
            $r = fgetc($this->stream);
            $char = ord($r);
            if ($char) {
                $bytes[] = $char;
            }
        } while ($char);
        //Note, buffer can contain multiple characters at once
        $this->buffer = $bytes;
    }

    public function close()
    {
        if ($this->isStreamWorking($this->stream)) {
            Output::get()->enableCursor();
            fclose($this->stream);
        }
    }

    private function isStreamWorking($stream): bool
    {
        return is_resource($stream) && !feof($stream);
    }

}