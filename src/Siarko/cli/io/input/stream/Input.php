<?php


namespace Siarko\cli\io\input\stream;


use Exception;
use Siarko\cli\bootstrap\Bootstrap;

class Input
{

    private $stream = null;

    /* @var $buffer mixed */
    private $buffer = false;

    public function __construct()
    {
        system(
            'stty cbreak -echo'
        );
        $this->initStream();
    }

    private function initStream()
    {
        if (!$this->isStreamWorking(STDIN)) {
            return false;
        }
        stream_set_blocking(STDIN, 0);
        $this->stream = STDIN;
        return true;
    }

    public function isBufferEmpty()
    {
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
        if (!$this->isStreamWorking($this->stream) && !$this->initStream()) {
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
        $bytes = [];
        do {
            $char = ord(fgetc($this->stream));
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
            system(
                'stty -cbreak echo'
            );
            fclose($this->stream);
        }
    }

    private function isStreamWorking($stream): bool
    {
        return is_resource($stream) && !feof($stream);
    }

}