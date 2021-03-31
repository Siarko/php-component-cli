<?php


namespace Siarko\cli\io\output;


use Siarko\cli\bootstrap\exceptions\ParamTypeException;
use Siarko\cli\io\output\devices\Stdout;
use Siarko\cli\io\output\exceptions\LayerIndexIncorrectException;
use Siarko\cli\util\Vec;

class DefaultOutputBuffer implements IOutBuffer
{
    const INDEX_POSITION = 'p';
    const INDEX_CONTENT = 'c';
    const STANDARD = 's';
    const LAYERED = 'l';

    private array $changes;
    /**
     * @var null|Vec
     */
    private $lastPosition = null;
    private $position = null;

    public function __construct()
    {
        $this->flush();
    }

    /**
     * @param $content
     * @param null|Vec $position
     * @throws ParamTypeException
     */
    function write($content, $position = null)
    {
        $content = (string)$content;
        if (is_null($position)) {
            $position = $this->getPrintPosition();
        }
        $this->lastPosition = clone $position;
        $this->lastPosition->increment(mb_strlen($this->getSanitizedString($content)));
        $this->addBufferUpdate($content, $position, self::STANDARD);
    }

    /**
     * @param $content
     * @param int $layer
     * @param null $position
     * @return mixed|void
     * @throws ParamTypeException|LayerIndexIncorrectException
     */
    function writeToLayer($content, int $layer, $position = null)
    {
        $content = (string)$content;
        if (is_null($position)) {
            $position = $this->getPrintPosition();
        }
        $this->lastPosition = clone $position;
        $this->lastPosition->increment(mb_strlen($this->getSanitizedString($content)));
        $this->addBufferUpdate($content, $position, self::LAYERED, $layer);
    }

    /**
     * Get position for next print
     * @return Vec|null
     * @throws ParamTypeException
     */
    private function getPrintPosition(): ?Vec
    {
        if ($this->position) {
            $result = $this->position;
            $this->position = null;
            return $result;
        } else {
            if ($this->lastPosition) {
                return $this->lastPosition;
            } else {
                return Stdout::get()->getPosition();
            }
        }
    }

    /**
     * Add new update for buffer
     * @param $content
     * @param $position
     * @param $type
     * @param int|null $layer
     * @throws LayerIndexIncorrectException
     */
    private function addBufferUpdate($content, $position, $type, ?int $layer = null)
    {
        $content = [
            self::INDEX_POSITION => $position,
            self::INDEX_CONTENT => $content
        ];
        if ($type == self::STANDARD) {
            $this->changes[self::STANDARD][] = $content;
        }
        if ($type == self::LAYERED) {
            if (!is_int($layer)) {
                throw new LayerIndexIncorrectException();
            }
            if(!array_key_exists($layer, $this->changes[self::LAYERED])){
                $this->changes[self::LAYERED][$layer] = [];
            }
            $this->changes[self::LAYERED][$layer][] = $content;
        }
    }

    /**
     * Remove all bash color/style special characters from string
     * @param $string
     * @return string|string[]|null
     */
    private function getSanitizedString($string)
    {
        return preg_replace('/(\\033\[.+m)/U', '', $string);
    }

    /**
     * @return bool
     * @throws ParamTypeException
     */
    public function apply()
    {
        $layers = $this->changes[self::LAYERED];
        ksort($layers);
        foreach ($layers as $layer) {
            foreach ($layer as $change) {
                Stdout::get()->setPosition($change[self::INDEX_POSITION]);
                Stdout::get()->print($change[self::INDEX_CONTENT]);
            }
        }

        foreach ($this->changes[self::STANDARD] as $change) {
            Stdout::get()->setPosition($change[self::INDEX_POSITION]);
            Stdout::get()->print($change[self::INDEX_CONTENT]);
        }
        $this->flush();
        return true;
    }

    /**
     * @return mixed|void
     */
    public function flush()
    {
        $this->changes = [
            self::STANDARD => [],
            self::LAYERED => []
        ];
    }

    /**
     * @return bool
     */
    public function changed(): bool
    {
        return count($this->changes[self::STANDARD]) > 0 || count($this->changes[self::LAYERED]) > 0;
    }

    /**
     * Set cursor position for next print
     * @param $x Vec|int
     * @param $y int|null
     */
    public function setCursorPosition($x, $y = null)
    {
        if ($x instanceof Vec) {
            $this->position = $x;
        } else {
            $this->position = new Vec($x, $y);
        }
        $this->lastPosition = clone $this->position;
    }

    /**
     * return cursor position after last print
     * @return Vec|null
     */
    public function getCursorPosition()
    {
        return $this->lastPosition;
    }
}