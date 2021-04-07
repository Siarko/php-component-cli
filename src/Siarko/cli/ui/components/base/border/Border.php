<?php


namespace Siarko\cli\ui\components\base\border;

use Siarko\cli\io\output\StyledText;
use Siarko\cli\io\output\styles\FontStyles;
use Siarko\cli\io\output\styles\TextColor;
use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\util\BoundingBox;

/**
 * @method Border setUp(string $style)
 * @method Border setDown(string $style)
 * @method Border setLeft(string $style)
 * @method Border setRight(string $style)
 * @method Border setLeftUp(string $style)
 * @method Border setLeftDown(string $style)
 * @method Border setRightUp(string $style)
 * @method Border setRightDown(string $style)
 *
 * @method string getUp()
 * @method string getDown()
 * @method string getLeft()
 * @method string getRight()
 * @method string getLeftUp()
 * @method string getLeftDown()
 * @method string getRightUp()
 * @method string getRightDown()
 *
 * @method int getUpLen()
 * @method int getDownLen()
 * @method int getLeftLen()
 * @method int getRightLen()
 * @method int getLeftUpLen()
 * @method int getLeftDownLen()
 * @method int getRightUpLen()
 * @method int getRightDownLen()
 *
 * */
class Border
{

    private TextColor $color;
    private string $title = '';

    private array $borderElements = [
        'Up' => '',
        'Down' => '',
        'Left' => '',
        'Right' => '',
        'LeftUp' => '',
        'LeftDown' => '',
        'RightUp' => '',
        'RightDown' => '',
    ];

    private array $lengths = [
        'Up' => 0,
        'Down' => 0,
        'Left' => 0,
        'Right' => 0,
        'LeftUp' => 0,
        'LeftDown' => 0,
        'RightUp' => 0,
        'RightDown' => 0,
    ];

    private array $calculatedShrink = [
        'Up' => 0,
        'Down' => 0,
        'Left' => 0,
        'Right' => 0
    ];

    public function __construct()
    {
        $this->color = TextColor::WHITE();
    }

    public function __call($name, $value)
    {
        if (mb_substr($name, 0, 3) == 'set') {
            $index = mb_substr($name, 3);
            if (array_key_exists($index, $this->borderElements)) {
                $this->borderElements[$index] = $value[0];
                $this->lengths[$index] = mb_strlen($value[0]);
                if ($index == 'Up' || $index == 'LeftUp' || $index == 'RightUp') {
                    $this->_shrinkTop();
                }
                if ($index == 'Down' || $index == 'LeftDown' || $index == 'RightDown') {
                    $this->_shrinkBottom();
                }
                if ($index == 'Left' || $index == 'LeftDown' || $index == 'LeftUp') {
                    $this->_shrinkLeft();
                }
                if ($index == 'Right' || $index == 'RightDown' || $index == 'RightUp') {
                    $this->_shrinkRight();
                }
            } else {
                throw new \Exception("This part of border does not exist!: {$index}");
            }
            return $this;
        }
        if (substr($name, 0, 3) == 'get') {
            $getLen = false;
            if (substr($name, strlen($name) - 3) == 'Len') {
                $index = substr($name, 3, strlen($name) - 6);
                $getLen = true;
            } else {
                $index = substr($name, 3);
            }
            if (array_key_exists($index, $this->borderElements)) {
                if ($getLen) {
                    return $this->lengths[$index];
                }
                return $this->borderElements[$index];
            } else {
                throw new \Exception("This part of border does not exist!: {$index}");
            }
        }
        return $this;

    }

    /**
     * Calculate width of border on top
     */
    public function _shrinkTop()
    {
        $top = mb_strlen($this->getUp());
        $corner = mb_strlen($this->getLeftUp());
        if ($corner > $top) {
            $top = $corner;
        }
        $corner = mb_strlen($this->getRightUp());
        if ($corner > $top) {
            $top = $corner;
        }
        $this->calculatedShrink['Up'] = $top;
    }

    /**
     * Get width of border on top
     */
    public function collapseTop(): int
    {
        return $this->calculatedShrink['Up'];
    }

    /**
     * Calculate width of border on bottom
     */
    public function _shrinkBottom()
    {
        $bottom = mb_strlen($this->getDown());
        $corner = mb_strlen($this->getLeftDown());
        if ($corner > $bottom) {
            $bottom = $corner;
        }
        $corner = mb_strlen($this->getRightDown());
        if ($corner > $bottom) {
            $bottom = $corner;
        }
        $this->calculatedShrink['Down'] = $bottom;
    }

    /**
     * Get width of border on bottom
     */
    public function collapseBottom(): int
    {
        return $this->calculatedShrink['Down'];
    }

    /**
     * Calculate width of border on left side
     */
    public function _shrinkLeft()
    {
        $left = mb_strlen($this->getLeft());
        $corner = mb_strlen($this->getLeftUp());
        if ($corner > $left) {
            $left = $corner;
        }
        $corner = mb_strlen($this->getLeftDown());
        if ($corner > $left) {
            $left = $corner;
        }
        $this->calculatedShrink['Left'] = $left;
    }

    /**
     * Get width of border on left
     */
    public function collapseLeft(): int
    {
        return $this->calculatedShrink['Left'];
    }

    /**
     * Calculate width of border on right side
     */
    public function _shrinkRight()
    {
        $right = mb_strlen($this->getRight());
        $corner = mb_strlen($this->getRightUp());
        if ($corner > $right) {
            $right = $corner;
        }
        $corner = mb_strlen($this->getRightDown());
        if ($corner > $right) {
            $right = $corner;
        }
        $this->calculatedShrink['Right'] = $right;
    }

    /**
     * Get width of border on right
     */
    public function collapseRight(): int
    {
        return $this->calculatedShrink['Right'];
    }

    /**
     * Get string for top row of box, with background
     * @param BaseComponent $component
     * @param $targetWidth
     * @return StyledText
     */
    public function getTopBorderRow(BaseComponent $component, $targetWidth): StyledText
    {
        if ($this->collapseTop() == 0) {
            return new StyledText('');
        }
        return $this->getBorderRow(
            $component,
            $targetWidth - ($this->getLeftUpLen() + $this->getRightUpLen()),
            $this->getLeftUp(),
            $this->getRightUp(),
            !strlen($this->getUp()) ? ' ' : $this->getUp(),
            $this->getTitle()
        );
    }

    /**
     * Get string for bottom row of box, with background
     * @param BaseComponent $component
     * @param $targetWidth
     * @return StyledText
     */
    public function getBottomBorderRow(BaseComponent $component, $targetWidth): StyledText
    {
        if ($this->collapseBottom() == 0) {
            return new StyledText('');
        }
        return $this->getBorderRow(
            $component,
            $targetWidth - ($this->getLeftDownLen() + $this->getRightDownLen()),
            $this->getLeftDown(),
            $this->getRightDown(),
            !mb_strlen($this->getDown()) ? ' ' : $this->getDown()
        );
    }

    /**
     * Get string for middle rows of box, with background
     * @param BaseComponent $component
     * @param $targetWidth
     * @return StyledText
     */
    public function getMiddleBorderRow(BaseComponent $component, $targetWidth): StyledText
    {
        return $this->getBorderRow(
            $component,
            $targetWidth - ($this->getLeftLen() + $this->getRightLen()),
            $this->getLeft(),
            $this->getRight()
        );
    }

    /**
     * @param BaseComponent $component
     * @param int $targetWidth
     * @param string $char1
     * @param string $char2
     * @param string $contentSign
     * @return StyledText
     */
    public function getBorderRow(
        BaseComponent $component,
        int $targetWidth,
        string $char1,
        string $char2,
        string $contentSign = ' ',
        string $_title = null
    ): StyledText {
        $text = $char1 . str_repeat($contentSign, $targetWidth) . $char2;
        if (!is_null($_title) && mb_strlen($_title)) {
            $text = $this->insertTitle($text, $_title);
        }
        $rowString = new StyledText($text);
        $rowString->setTextColor($this->getColor());
        $rowString->setBackgroundColor($component->getBackgroundColor());
        return $rowString;
    }

    /**
     * @param string $text
     * @param string $title
     * @return string
     */
    private function insertTitle(string $text, string $title, int $rightMargin = 2): string
    {
        $tl = mb_strlen($title);
        $rl = mb_strlen($text);
        if ($tl > ($rl + $rightMargin + 1)) {
            return $text; //do not insert title - top border is too short
        }
        return mb_substr($text, 0, $rightMargin).$title.mb_substr($text, $rightMargin+$tl);
    }

    /**
     * @return TextColor
     */
    public function getColor(): TextColor
    {
        return $this->color;
    }

    /**
     * @param TextColor $color
     * @return Border
     */
    public function setColor(TextColor $color): Border
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return BaseComponent
     */
    public function setTitle(string $title): Border
    {
        $this->title = $title;
        return $this;
    }

}