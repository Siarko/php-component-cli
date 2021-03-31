<?php

namespace Siarko\cli\io\output;

interface IOutBuffer
{

    /**
     * Set cursor position for next write operation
     * Starts at [1,1]
     * @param $x
     * @param $y
     * @return mixed
     */
    function setCursorPosition($x, $y);

    /**
     * Write content to output
     * If position is supplied, write at position
     * @param $content
     * @param null $position
     * @return mixed
     */
    function write($content, $position = null);

    /**
     * Same as Write but layered
     * @param $content
     * @param $layer
     * @param null $position
     * @return mixed
     */
    function writeToLayer($content, int $layer, $position = null);

    /**
     * Apply changes from buffer to output device
     * Return success flag
     * @return bool
     */
    function apply();

    /**
     * Return if buffer was changed and can update output
     * @return bool
     */
    function changed(): bool;

    /**
     * @return mixed
     */
    function flush();

}