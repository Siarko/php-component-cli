<?php


namespace Siarko\cli\io\input\event;


use Siarko\cli\util\Cacheable;

class KeyDownEvent
{
    use Cacheable;

    //cache keys
    const CACHE_CODE = 'code';
    const CACHE_CHAR = 'char';
    const CACHE_IS_ANY_ARROW = 'any_arrow';
    const CACHE_UNICODE = 'unicode';
    const CACHE_ALT = 'alt';
    const CACHE_SPECIAL = 'special';

    private $codes;

    public function __construct($keyCodes = [])
    {
        $this->codes = $keyCodes;
    }

    /**
     * Get main (first) detected key code
     * @return mixed
     */
    public function getCode()
    {
        if (!$this->cacheExists(self::CACHE_CODE)) {
            if ($this->isAlt()) {
                $this->setCache(self::CACHE_CODE, $this->codes[1]);
            }
            $this->setCache(self::CACHE_CODE, $this->codes[0]);
        }
        return $this->getCache(self::CACHE_CODE);
    }

    /**
     * Get all detected keycodes
     * @return array|mixed
     */
    public function getAllCodes(): array
    {
        return $this->codes;
    }

    /**
     * Returns any detected character from keycodes
     * @return string|null
     */
    public function getChar(): ?string
    {
        if (!$this->cacheExists(self::CACHE_UNICODE) && $this->isChar()) {
            if ($this->isUnicode()) {
                $this->setCache(self::CACHE_CHAR, $this->getUnicode());
            } else {
                $this->setCache(self::CACHE_CHAR, chr($this->getCode()));
            }
            return $this->getCache(self::CACHE_CHAR);
        }
        return null;
    }

    /**
     * Is key a writable character
     * @param null $char
     * @return bool
     */
    public function isChar($char = null): bool
    {
        if(!$this->isSpecial() || $this->isAlt()){
            if(is_null($char)){
                return true;
            }else{
                return $this->getChar() == $char;
            }
        }
        return false;
    }

    /**
     * Check if is alt-key combination
     * @return bool
     */
    public function isAlt(): bool
    {
        if (!$this->cacheExists(self::CACHE_ALT)) {
            if (count($this->codes) == 2) {
                $this->setCache(self::CACHE_ALT, ($this->codes[0] == KeyCodes::SPECIAL));
            } else {
                foreach (KeyCodes::ALT_KEYS as $ALT_KEY) {
                    if ($this->intersectSets($ALT_KEY)) {
                        $this->setCache(self::CACHE_ALT, true);
                        break;
                    }
                }
            }
            $this->setCache(self::CACHE_ALT, false);
        }
        return $this->getCache(self::CACHE_ALT);

    }

    public function isCtrl(): bool
    {
        return (count($this->codes) == 1 && in_array($this->codes[0], KeyCodes::CTRL_KEYS));
    }

    /**
     * Is functional key - Insert,arrows,etc
     * @return bool
     */
    public function isSpecial(): bool
    {
        if (!$this->cacheExists(self::CACHE_SPECIAL)) {
            if ($this->getCode() == KeyCodes::SPECIAL || $this->isCtrl()) {
                $this->setCache(self::CACHE_SPECIAL, true);
            } else {
                foreach (KeyCodes::SPECIAL_KEYS as $SPECIAL_KEY) {
                    if ($this->isKey($SPECIAL_KEY)) {
                        return $this->setCache(self::CACHE_SPECIAL, true);
                    }
                }
                $this->setCache(self::CACHE_SPECIAL, false);
            }
        }
        return $this->getCache(self::CACHE_SPECIAL);
    }

    /** Is unicode or diactric
     * @return bool
     */
    public function isUnicode(): bool
    {
        return count($this->codes) > 1 && $this->codes[0] != KeyCodes::SPECIAL;
    }

    /**
     * @param $codeSet int|int[]
     * @return bool
     */
    public function isKey($codeSet): bool
    {
        $set = $this->codes;
        if (!is_array($codeSet)) {
            $codeSet = [$codeSet];
        } else {
            if ($this->isAlt() && count($this->codes) > 2) {
                //remove alt bytes from code set
                $count = count($this->codes);
                $set = array_slice($this->codes, 0, $count - 3);
                $set[] = $this->codes[$count - 1];
            }
        }
        if(count($set) != count($codeSet)){ return false; }
        return count(array_intersect($set, $codeSet)) == count($codeSet);
    }

    public function intersectSets($codeSet): bool
    {
        $set = $this->codes;
        if (!is_array($codeSet)) {
            $codeSet = [$codeSet];
        }
        return count(array_intersect($set, $codeSet)) == count($set);
    }

    /**
     * @return bool
     */
    public function isAnyArrow(): bool
    {
        if (!$this->cacheExists(self::CACHE_IS_ANY_ARROW)) {
            foreach (KeyCodes::ARROWS as $CODE_SET) {
                foreach ($CODE_SET as $subSet) {
                    if ($this->isKey($subSet)) {
                        $this->setCache(self::CACHE_IS_ANY_ARROW, true);
                        return $this->getCache(self::CACHE_IS_ANY_ARROW);
                    }
                }
            }
            $this->setCache(self::CACHE_IS_ANY_ARROW, false);
        }
        return $this->getCache(self::CACHE_IS_ANY_ARROW);
    }

    /**
     * Returns unicode character from keycodes
     * @return string
     */
    private function getUnicode(): string
    {
        if (!$this->cacheExists(self::CACHE_UNICODE)) {
            $c = $this->codes;
            if ($this->isAlt()) {
                $c = [$this->codes[1]];
            }
            $char = '';
            for ($i = 0; $i < count($c); $i++) {
                $char .= chr($c[$i]);
            }
            $this->setCache(self::CACHE_UNICODE, $char);
        }
        return $this->getCache(self::CACHE_UNICODE);
    }

}