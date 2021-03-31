<?php


namespace Siarko\cli\ui\components;


use Siarko\cli\io\input\event\KeyCodes;
use Siarko\cli\io\input\event\KeyDownEvent;
use Siarko\cli\io\Output;
use Siarko\cli\io\output\StyledText;
use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\io\output\styles\FontStyles;
use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\util\BoundingBox;

class Checkbox extends Component
{
    protected bool $checked = false;

    protected const SIGN_CHECKED = '✖';
    protected const SIGN_UNCHECKED = '☐';

    protected StyledText $styledText;

    public function __construct()
    {
        parent::__construct();
        $this->styledText = new StyledText();
    }

    public function drawContent(BoundingBox $contentBox)
    {
        $this->styledText->setText(($this->isChecked()) ? self::SIGN_CHECKED : self::SIGN_UNCHECKED);
        $this->styledText->setBackgroundColor($this->getBackgroundColor());
        Output::get()->getOutputBuffer()->write($this->styledText, $contentBox->getPosition());
    }

    /**
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    /**
     * @param bool $checked
     */
    public function setChecked(bool $checked): void
    {
        $this->checked = $checked;
        $this->setValid(false);
    }

    protected function onKeyDown(KeyDownEvent $event): bool
    {
        if($event->isKey(KeyCodes::ENTER)){
            $this->setChecked(!$this->isChecked());
        }
        return parent::onKeyDown($event);
    }

}