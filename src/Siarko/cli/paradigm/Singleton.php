<?php


namespace Siarko\cli\paradigm;

use Siarko\cli\bootstrap\exceptions\ParamTypeException;

trait Singleton
{
    /* @var static */
    protected static $instance = null;

    /**
     * @param array $params
     * @return static
     * @throws ParamTypeException
     */
    public static function get(array $params = [])
    {
        if(!is_array($params)){
            throw new ParamTypeException("Incorrect param for constructor of ".self::class);
        }
        if(!self::_initialized()){
            $sortedParams = self::_sortParams($params);
            if ($sortedParams) {
                static::$instance = new static(...$sortedParams);
            } else {
                static::$instance = new static();
            }
        }
        return static::$instance;
    }

    private static function _sortParams(array $params){
        $f = new \ReflectionClass(static::class);
        $c = $f->getConstructor();
        if($c){
            $sortedValues = [];
            foreach ($c->getParameters() as $constructorParam) {
                if (array_key_exists($constructorParam->getName(), $params)) {
                    $sortedValues[] = $params[$constructorParam->getName()];
                } else {
                    try {
                        $sortedValues[] = $constructorParam->getDefaultValue();
                    } catch (\ReflectionException $e) {
                        $sortedValues[] = null;
                    }
                }
            }
            return $sortedValues;
        }else{
            return null;
        }
    }

    public static function _initialized()
    {
        return (static::$instance != null);
    }

}