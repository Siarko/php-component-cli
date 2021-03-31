<?php


namespace Siarko\cli\util\unit;


abstract class Unit
{
    private $value;
    private string $suffix;

    public static $UNITS = [
        'px' => Pixel::class,
        '%' => Percent::class
    ];

    protected function __construct($suffix, $object)
    {
        $this->setSuffix($suffix);
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     */
    private function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function set($value): void
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     * @return mixed|null
     * @throws UnknownUnitException
     */
    public static function stringToUnit(string $value)
    {
        $parts = preg_split('#(?<=\d)(?=[a-z%])#i', $value);
        if(count($parts) != 2){
            return null;
        }
        foreach (Unit::$UNITS as $name => $UNIT) {
            if($parts[1] == $name){
                return new $UNIT($parts[0]);
            }
        }
        throw new UnknownUnitException();
    }
}