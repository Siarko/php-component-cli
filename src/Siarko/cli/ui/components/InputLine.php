<?php

namespace Siarko\cli\ui\components;

use Siarko\cli\io\input\event\KeyCodes;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\Output;
use Siarko\cli\io\output\StyledText;
use Siarko\cli\io\output\styles\FontStyles;
use Siarko\cli\io\output\styles\TextColor;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\util\BoundingBox;

/**
 * Class for Input Line Ui component
 * Handles single-line input for characters
 * */
class InputLine extends Component
{

    //full text content
    private string $content = '';
    //current cursor position
    private int $cursorPosition = 0;

    //padding of text window on the right
    private int $rightTextPadding = 0;
    //padding of text window on the left
    private int $leftTextPadding = 0;
    //cursor padding before scrolling occurs
    private int $cursorScrollPadding = 3;

    private TextColor $textColor;

    private \Clipboard $clipboard;


    public function __construct()
    {
        parent::__construct();
        $this->setTextColor(TextColor::WHITE());
        $this->clipboard = new \Clipboard();
        //$this->getSizing()->setMaxSize(-1, 1);
    }


    public function drawContent(BoundingBox $contentBox)
    {
        //content length - all text length
        $contentLength = mb_strlen($this->content);
        //length of text window
        $textWindowLen = $contentBox->getWidth() - 1 - $this->rightTextPadding - $this->leftTextPadding;

        //start position of text window in text
        $textWindowPos = $this->cursorPosition + $this->cursorScrollPadding - $textWindowLen;
        if ($textWindowPos < 0 || $textWindowLen > $contentLength) {
            $textWindowPos = 0;
        }else if ($textWindowPos > $contentLength - $textWindowLen) {
            $textWindowPos = $contentLength - $textWindowLen;
        }
        //calculate cursor position in text window
        $visibleCursorPosition = $this->cursorPosition - $textWindowPos;
        //cut a piece of text that should be visible in text window
        $visibleText = mb_substr($this->content, $textWindowPos, $textWindowLen);
        $visibleTextLen = mb_strlen($visibleText);
        //calculate number of filler characters (spaces)
        $fillerLen = $textWindowLen-$visibleTextLen;
        $fillerLen = $fillerLen <= 0 ? 1 : $fillerLen+1;
        //create filler text
        $fillerText = str_repeat(' ', $fillerLen);
        //append filler to whole text
        $visibleText .= $fillerText;

        //cut text, give it background and mark cursor by inverting color
        $background = (new StyledText(mb_substr($visibleText, 0, $visibleCursorPosition)))
            ->setBackgroundColor($this->getBackgroundColor())->setTextColor($this->textColor);
        $background .= (new StyledText(mb_substr($visibleText, $visibleCursorPosition, 1)))
            ->addFontStyle(FontStyles::INVERTED());
        $background .= (new StyledText(mb_substr($visibleText, $visibleCursorPosition+1)))
            ->setBackgroundColor($this->getBackgroundColor())->setTextColor($this->textColor);
        //write text to screen buffer
        $buffer = Output::get()->getOutputBuffer();
        $buffer->setCursorPosition($contentBox->getX() + $this->leftTextPadding, $contentBox->getY());
        $buffer->writeToLayer($background, $this->getLayer());
    }

    /**
     * @return TextColor
     */
    public function getTextColor(): TextColor
    {
        return $this->textColor;
    }

    /**
     * @param TextColor $textColor
     * @return InputLine
     */
    public function setTextColor(TextColor $textColor): InputLine
    {
        $this->textColor = $textColor;
        return $this;
    }

    /**
     * Move cursor to the left, stop on beginning
     */
    protected function moveCursorLeft()
    {
        if ($this->cursorPosition > 0) {
            $this->cursorPosition--;
        }
    }

    /**
     * Move cursor to the right, stop at the end
     */
    protected function moveCursorRight()
    {
        if ($this->cursorPosition < mb_strlen($this->content)) {
            $this->cursorPosition++;
        }
    }

    /**
     * Add character to text in current cursor position
     * @param string $char
     */
    protected function addChar(string $char)
    {
        $prefix = mb_substr($this->content, 0, $this->cursorPosition);
        $suffix = mb_substr($this->content, $this->cursorPosition);
        $this->content = $prefix . $char . $suffix;
        $this->cursorPosition++;
    }

    /**
     * Remove one character on the left
     */
    protected function removeChar()
    {
        if ($this->cursorPosition == 0) {
            return;
        }
        $this->content = mb_substr($this->content, 0, $this->cursorPosition - 1) .
            mb_substr($this->content, $this->cursorPosition);
        $this->cursorPosition--;
    }

    /**
     * Remove one character on the right
     */
    private function removeCharInFront()
    {
        if ($this->cursorPosition == mb_strlen($this->content)) {
            return;
        }
        $this->content = mb_substr($this->content, 0, $this->cursorPosition) .
            mb_substr($this->content, $this->cursorPosition+1);
    }

    private function pasteClipboard(): bool
    {
        if($this->clipboard->isUnsupported()){ return false;}
        $chars = mb_str_split($this->clipboard->readAll());
        foreach ($chars as $char) {
            $this->addChar($char);
        }
        return true;
    }

    /**
     * Handle keyboard input
     * @param KeyDownEvent $event
     * @return bool
     */
    protected function onKeyDown(KeyDownEvent $event): bool
    {
        if ($event->isChar()) {
            $this->addChar($event->getChar());
            $this->setValid(false);
            $this->updateContent();
            return true;
        }
        if ($event->isKey(KeyCodes::BACKSPACE)) {
            $this->removeChar();
            $this->setValid(false);
            $this->updateContent();
            return true;
        }
        if ($event->isKey(KeyCodes::DELETE)) {
            $this->removeCharInFront();
            $this->setValid(false);
            $this->updateContent();
            return true;
        }
        if ($event->isKey(KeyCodes::ARROW_LEFT)) {
            $this->moveCursorLeft();
            $this->setValid(false);
            $this->updateContent();
            return true;
        }
        if ($event->isKey(KeyCodes::ARROW_RIGHT)) {
            $this->moveCursorRight();
            $this->setValid(false);
            $this->updateContent();
            return true;
        }
        //CTRL handling
        if($event->isKey(KeyCodes::CTRL_V)){
            $this->pasteClipboard();
            $this->setValid(false);
            $this->updateContent();
            return true;
        }
        if($event->isKey(KeyCodes::CTRL_C)){
            //copy to clipboard handling
            return true;
        }
        if($event->isKey(KeyCodes::CTRL_ARROW_LEFT)){
            return true;
        }
        if($event->isKey(KeyCodes::CTRL_ARROW_RIGHT)){
            return true;
        }
        if($event->isKey(KeyCodes::CTRL_BACKSPACE)){
            return true;
        }
        return parent::onKeyDown($event);
    }

}