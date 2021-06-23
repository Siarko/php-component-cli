<?php


namespace Siarko\cli\ui\components;


use Siarko\cli\io\Output;
use Siarko\cli\io\output\StyledText;
use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\ui\layouts\align\HorizontalAlign;
use Siarko\cli\util\BoundingBox;
use Siarko\cli\util\Vec;

class TextComponent extends Component
{

    private StyledText $text;

    private HorizontalAlign $textAlign;

    public function __construct(string $text = '')
    {
        parent::__construct();
        $this->textAlign = HorizontalAlign::LEFT();
        $this->text = new StyledText($text);
    }

    public function drawContent(BoundingBox $contentBox)
    {
        $out = Output::get()->getOutputBuffer();
        $outputText = $this->text;
        $x = 0;
        if (mb_strlen($this->text->getText()) >= $contentBox->getWidth()) {
            $outputText->setText(mb_substr($this->text->getText(), 0, $contentBox->getWidth()));
        } else {
            $x = $this->getXPos($contentBox->getWidth());
        }
        if(($c = $this->getBackgroundColor()) != null){
            $outputText->setBackgroundColor($c);
        }
        $out->writeToLayer($outputText, $this->getLayer(), new Vec($contentBox->getX() + $x, $contentBox->getY()));

    }

    private function getXPos(int $maxWidth)
    {
        $offset = 0;
        $len = mb_strlen($this->text->getText());
        if ($this->textAlign->equals(HorizontalAlign::MIDDLE())) {
            $offset = (int)$maxWidth / 2 - (int)($len / 2);
        }
        if ($this->textAlign->equals(HorizontalAlign::RIGHT())) {
            $offset = $maxWidth - $len;
        }
        return $offset;
    }

    /**
     * @return StyledText
     */
    public function getStyledText(): StyledText
    {
        return $this->text;
    }

    /**
     * @param StyledText|string $text
     * @return TextComponent
     */
    public function setText($text): TextComponent
    {
        if ($text instanceof StyledText) {
            $this->text = $text;
        }
        if (is_string($text)) {
            $this->text->setText($text);
        }
        return $this;
    }

    /**
     * @return HorizontalAlign
     */
    public function getTextAlign(): HorizontalAlign
    {
        return $this->textAlign;
    }

    /**
     * @param HorizontalAlign $textAlign
     * @return TextComponent
     */
    public function setTextAlign(HorizontalAlign $textAlign): TextComponent
    {
        $this->textAlign = $textAlign;
        return $this;
    }
}