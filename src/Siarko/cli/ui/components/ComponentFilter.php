<?php


namespace Siarko\cli\ui\components;


use Siarko\cli\ui\components\base\BaseComponent;

class ComponentFilter
{
    /**
     * @param bool $negate
     * @return \Closure
     */
    public static function visible($negate = false): \Closure
    {
        return function (BaseComponent $component) use ($negate){
            $prop = $component->isVisible();
            return (!$negate && $prop) || ($negate && !$prop);
        };
    }

    /**
     * @param false $negate
     * @return \Closure
     */
    public static function active($negate = false): \Closure
    {
        return function (BaseComponent $component) use ($negate){
            $prop = $component->isActive();
            return (!$negate && $prop) || ($negate && !$prop);
        };
    }

    /**
     * @param false $negate
     * @return \Closure
     */
    public static function floating($negate = false): \Closure
    {
        return function (BaseComponent $component) use ($negate){
            $prop = $component->isFloating();
            return (!$negate && $prop) || ($negate && !$prop);
        };
    }

    public static function rewriteKeys(array $a): array
    {
        $new = [];
        foreach ($a as $item) {
            $new[] = $item;
        }
        return $new;

    }
}