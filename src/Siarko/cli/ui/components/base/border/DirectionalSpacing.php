<?php


namespace Siarko\cli\ui\components\base\border;

/**
 * @method $this setUp(int $amount)
 * @method $this setDown(int $amount)
 * @method $this setLeft(int $amount)
 * @method $this setRight(int $amount)
 *
 * @method int getUp()
 * @method int getDown()
 * @method int getLeft()
 * @method int getRight()
 *
 * */
class DirectionalSpacing
{

    private array $values = [
        'Up' => 0,
        'Down' => 0,
        'Left' => 0,
        'Right' => 0,
    ];

    public function __construct($value = null)
    {
        if(!is_null($value)){
            $this->setDown($value);
            $this->setUp($value);
            $this->setLeft($value);
            $this->setRight($value);
        }
    }

    public function __call($name, $value)
    {
        $index = substr($name, 3);
        if(substr($name, 0, 3) == 'set'){
            if(array_key_exists($index, $this->values)){
                $this->values[$index] = $value[0];
            }else{
                throw new NoIndexException("This part of border does not exist!: {$index}");
            }
            return $this;
        }
        if(substr($name, 0, 3) == 'get'){
            if(array_key_exists($index, $this->values)){
                return $this->values[$index];
            }else{
                throw new NoIndexException("This part of border does not exist!: {$index}");
            }
        }
        return $this;

    }

}