<?php


namespace Siarko\cli\util\graphics;


use Siarko\cli\io\Output;
use Siarko\cli\io\output\IOutBuffer;
use Siarko\cli\io\output\StyledText;
use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\util\BoundingBox;

class Shape
{

    public static function rectangle(
        BoundingBox $boundingBox,
        $character = ' ',
        ?int $layer = null,
        ?BackgroundColor $backgroundColor = null,
        ?IOutBuffer $buffer = null
    ) {
        if (is_null($buffer)) {
            $buffer = Output::get()->getOutputBuffer();
        }
        if(mb_strlen($character) == 1){
            $character = str_repeat($character, $boundingBox->getWidth());
        }
        if($character instanceof StyledText){
            $line = $character;
        }else{
            $line = (new StyledText($character));
        }
        if(!is_null($backgroundColor)){
            $line->setBackgroundColor($backgroundColor);
        }
        if(is_int($layer)){
            for($y = 0; $y < $boundingBox->getHeight(); $y++){
                $buffer->writeToLayer($line, $layer, $boundingBox->getPosition()->increment(0, $y, false));
            }
        }else{
            for($y = 0; $y < $boundingBox->getHeight(); $y++){
                $buffer->write($line, $boundingBox->getPosition()->increment(0, $y, false));
            }
        }
    }
}