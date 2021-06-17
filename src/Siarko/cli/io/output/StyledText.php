<?php


namespace Siarko\cli\io\output;


use Siarko\cli\io\output\styles\BackgroundColor;
use Siarko\cli\io\output\styles\FontStyles;
use Siarko\cli\io\output\styles\TextColor;

class StyledText
{
    private string $text = '';
    private array $styles = [];
    private TextColor $textColor;
    private BackgroundColor $backgroundColor;

    function __construct($text = '')
    {
        $this->textColor = TextColor::WHITE();
        $this->backgroundColor = BackgroundColor::BLACK();
        $this->text = $text;
    }

    /**
     * @param TextColor $color
     * @return $this
     */
    public function setTextColor(TextColor $color): StyledText
    {
        $this->textColor = $color;
        return $this;
    }

    /**
     * @return styles\Color|string
     */
    public function getTextColor(): TextColor
    {
        return $this->textColor;
    }

    /**
     * @param BackgroundColor $color
     * @return $this
     */
    public function setBackgroundColor(BackgroundColor $color): StyledText
    {
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * @param FontStyles $style
     * @return $this
     */
    public function addFontStyle(FontStyles $style): StyledText
    {
        $this->styles[]  = $style;
        return $this;
    }

    private function parseUnicode(string $text){
        preg_match_all('/\\\u\d+/', $text, $matches);
        foreach ($matches[0] as $match) {
            $replacement = json_decode('"'.$match.'"');
            $text = str_replace($match, $replacement, $text);
        }
        return $text;
    }

    /**
     * @return string
     */
    private function getStylesString(): string
    {
        $s = $this->styles;
        if(!$this->backgroundColor->equals(BackgroundColor::TRANSPARENT())){
            $s[] = $this->backgroundColor;
        }
        $s[] = $this->textColor;
        return $this->createStyle($s);
    }

    public function build(): string
    {
        return $this->getStylesString().
            $this->parseUnicode($this->text).
            $this->createStyle([FontStyles::STYLE_RESET()->getValue()]);
    }


    public function __toString(): string
    {
        return $this->build();
    }

    /**
     * @param string[] $styles
     * @return string
     */
    protected function createStyle(array $styles): string
    {
        if(empty($styles)){
            return '';
        }
        $result = "\e[";
        for($i = 0; $i < count($styles); $i++){
            $result .= $styles[$i] . (($i < count($styles)-1) ? ';' : '');
        }
        return $result.'m';
    }

    /**
     * @return mixed|string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param mixed|string $text
     * @return StyledText
     */
    public function setText(string $text): StyledText
    {
        $this->text = $text;
        return $this;
    }

}