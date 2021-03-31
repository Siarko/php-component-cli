<?php

namespace Siarko\cli\util\volatile;

use Siarko\cli\bootstrap\Bootstrap;
use Siarko\cli\paradigm\Singleton;

/**
 * @method static self get()
 * */
class TmpFile
{

    use Singleton;

    private $files = [];

    public function __construct()
    {
        register_shutdown_function(function() {
            $this->cleanup();
        });
        pcntl_signal(SIGINT, function(){$this->cleanup();});
        pcntl_signal(SIGUSR1, function(){$this->cleanup();});
    }

    public function create(){
        $this->checkStructure();
        $name = $this->generateName();
        $root_url = Bootstrap::$ROOT_DIR.'/tmp/'.$name.'.tmp';
        $file = fopen($root_url, "w");
        $this->files[$name] = $file;
        return $root_url;
    }

    private function generateName($length = 10){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function checkStructure(){
        $path = Bootstrap::$ROOT_DIR.'/tmp';
        if(!file_exists($path)){
            mkdir($path);
        }
    }

    public function cleanup(){
        foreach ($this->files as $name => $handler) {
            $meta = stream_get_meta_data($handler);
            fclose($handler);
            unlink($meta['uri']);
        }
    }

}