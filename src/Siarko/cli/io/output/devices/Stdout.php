<?php


namespace Siarko\cli\io\output\devices;


use Siarko\cli\paradigm\Singleton;
use Siarko\cli\util\Vec;

class Stdout
{
    use Singleton;

    /**
     * @param $value
     */
    public function print($value){
        echo $value;
    }

    /**
     * @param int|Vec $x
     * @param int|null $y
     */
    public function setPosition($x, int $y = null){
        if($x instanceof Vec){
            $y = $x->getY();
            $x = $x->getX();
        }
        echo "\033[".((int)$y).";".((int)$x)."f";
    }

    /**
     * @return Vec
     */
    public function getPosition(){
        $ttyprops = trim(`stty -g`);
        system('stty -icanon');
        echo "\033[6n";
        $buff = "";
        while(strlen($buff) == 0){
            $buff = fread(STDIN, 16);
        }
        system("stty '$ttyprops'");
        preg_match('/.*\[(\d+);(\d+)R/', $buff, $matches);
        if(count($matches) == 3){
            return new Vec($matches[1], $matches[2]);
        }
        return new Vec();
    }

}