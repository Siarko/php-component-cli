<?php


namespace Siarko\cli\util;


class ArrayHelper
{

    public static function getValue(array $data, $path = null)
    {
        if(is_null($path)){
            return $data;
        }
        $steps = explode('/', $path);
        return self::_getValue($steps, $data);
    }

    private static function _getValue($keys, array $array){
        if(array_key_exists($keys[0], $array)){
            if(count($keys) == 1){
                return $array[$keys[0]];
            }else{
                $k = $keys[0];
                array_shift($keys);
                return self::_getValue($keys, $array[$k]);
            }
        }else{
            return null;
        }
    }

    public static function setValue(array &$array, $path, $value): array
    {
        if(is_null($path)){
            return $array = $value;
        }
        $steps = explode('/', $path);
        self::_setValue($array, $steps, $value);
        return $array;
    }

    private static function _setValue(array &$array, $steps, $value){
        $key = $steps[0];
        if(count($steps) == 1){
            $array[$key] = $value;
        }else{
            if(!array_key_exists($key, $array)){
                $array[$key] = [];
            }
            array_shift($steps);
            return self::_setValue($array[$key], $steps, $value);
        }
    }

    public static function deepClone(array $array): array{
        $result = [];
        foreach ($array as $key => $object) {
            if(is_array($object)){
                $result[$key] = self::deepClone($object);
            }elseif(is_object($object)){
                $result[$key] = clone $object;
            }else{
                $result[$key] = $object;
            }
        }
        return $result;
    }

}