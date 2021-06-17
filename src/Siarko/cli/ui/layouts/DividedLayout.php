<?php


namespace Siarko\cli\ui\layouts;


use Siarko\cli\ui\components\base\BaseComponent;
use Siarko\cli\ui\components\base\Component;
use Siarko\cli\ui\components\ComponentFilter;
use Siarko\cli\ui\exceptions\IncorrectProportionsException;
use Siarko\cli\ui\exceptions\TooFewProportionValues;
use Siarko\cli\util\ArrayHelper;
use Siarko\cli\util\unit\Percent;
use Siarko\cli\util\unit\Pixel;
use Siarko\cli\util\unit\Unit;
use Siarko\cli\util\unit\UnknownUnitException;

abstract class DividedLayout extends AbstractLayout
{

    //divisions set by user (used for activation/deactivation)
    protected array $originalDivisions = [];
    //divisions used for calculation
    protected array $divisions = [];
    //calculated values - from divisions
    protected array $calculatedSizes = [];
    //if divisions auto-generated
    protected bool $generated = false;

    //offset and width/height for getBB after calculateValueForComponent
    protected int $offset;
    protected int $value;

    /**
     * DividedLayout constructor.
     * @param array $divisions
     * @throws IncorrectProportionsException
     */
    public function __construct(array $divisions = [])
    {
        $this->setDivisions($divisions);
    }

    /**
     * @param array $divisions
     * @return $this
     * @throws IncorrectProportionsException|UnknownUnitException
     */
    public function setDivisions(array $divisions): DividedLayout
    {
        $this->setValid(false);
        $divisions = $this->getUnitArray($divisions);
        $this->originalDivisions = ArrayHelper::deepClone($divisions);
        $this->divisions = $divisions;
        $this->checkProportions();
        return $this;
    }

    /**
     * Generate unit array from string array
     * @param array $divisions
     * @return array
     * @throws UnknownUnitException
     */
    private function getUnitArray(array $divisions): array
    {
        $result = [];
        $wildcardIndices = [];
        foreach ($divisions as $key => $item) {
            if ($item instanceof Unit) {
                $result[$key] = $item;
                continue;
            }
            if ($item == '*') {
                $wildcardIndices[] = $key;
                $result[$key] = null;
            } else {
                $result[$key] = Unit::stringToUnit($item);
            }
        }

        $cwi = count($wildcardIndices);
        if ($cwi) {
            $values = $this->getWildcardPercent($result, $cwi);
            foreach ($values as $k => $value) {
                $result[$k] = new Percent($value);
            }
        }

        return $result;

    }

    /**
     * Calculate percent value for wildcards (*) in constructor
     * @param array $values
     * @param int $wildcardCount
     * @return array
     */
    private function getWildcardPercent(array $values, int $wildcardCount): array
    {
        $percent = 0;
        $result = [];
        foreach ($values as $k => $item) {
            if ($item instanceof Percent) {
                $percent += $item->getValue();
            } elseif (is_null($item)) {
                $result[$k] = 0;
            }
        }
        $percent = 100 - $percent;
        $divided = (int)($percent / $wildcardCount);
        $reminder = $percent - ($divided * $wildcardCount);
        foreach ($result as $k => $v) {
            $result[$k] = $divided;
        }
        if ($reminder > 0) {
            $result[end($result)] += $reminder;
        }
        return $result;
    }

    /**
     * Check if proportions add up to 100% (if any % are set)
     * @throws IncorrectProportionsException
     */
    private function checkProportions()
    {
        $percents = array_map(
            function ($e) {
                return $e->getValue();
            },
            array_filter($this->divisions, function (Unit $unit) {
                return $unit instanceof Percent;
            })
        );
        $sum = array_sum($percents);
        if (count($percents) > 0 && $sum != 100) {
            throw new IncorrectProportionsException("Sum of proportions is {$sum} != 100");
        }
    }

    /* === DRAWING CALCULATION ===*/

    /**
     * Calculate value for component (width or height)
     * @param BaseComponent $component
     * @param int $targetValue
     * @throws TooFewProportionValues
     */
    protected function calculateValuesForComponent(BaseComponent $component, int $targetValue)
    {
        //count of active and not floating components
        $childCount = count($this->parentComponent->getChildren(
            ComponentFilter::active(), ComponentFilter::floating(true)
        ));
        //count of all children but not floating (modals)
        $allChildCount = count($this->parentComponent->getChildren(ComponentFilter::floating(true)));
        $proportionCount = count($this->divisions);
        $childIndex = $this->getComponentIndex($component);

        //count of active children is not equal to calculated divisions
        //check if user-defined proportion count is correct and fix this
        if(
            $allChildCount == count($this->originalDivisions) &&
            $proportionCount != $childCount &&
            $this->fixInactiveDivisions()
        ){
            $proportionCount = $childCount;
        }

        if ($proportionCount == 0) { //no proportions set, divide into equal pieces
            $proportionCount = $childCount;
            $this->generateDivisions($childCount);
        }

        if ($proportionCount < $childCount) {
            throw new TooFewProportionValues("Child count: {$childCount} is not equal proportion count {$proportionCount}");
        }

        //recalculate sizes for all components
        $this->calculateSizes($targetValue);
        //proportions set, calculate width
        $this->value = $this->calculatedSizes[$childIndex];
        $this->offset = array_sum((array)array_slice($this->calculatedSizes, 0, $childIndex));
    }

    /**
     * Calculate all sizes for components
     * @param $maxSize
     */
    protected function calculateSizes($maxSize)
    {
        //calculate only once until invalidation
        if ($this->isValid()) {
            return;
        } else {
            $this->setValid(true);
        }
        $this->calculatedSizes = [];
        //correct sizing for border sizes
        $this->correctPixelsForBorders();
        //sum all pixels
        $pixelsTaken = array_sum(
            array_map(
                function ($e) {
                    return $e->getValue();
                },
                array_filter($this->divisions, function ($e) {
                    return $e instanceof Pixel;
                })
            )
        );
        //max size is reduced by pixel sizes -> for percent calculation
        $maxSize -= $pixelsTaken;
        $percentPropCount = 0;
        $percentIndexes = [];

        foreach ($this->divisions as $key => $division) {
            if ($division instanceof Pixel) {
                //value is in pixels so it's already calculated
                $this->calculatedSizes[$key] = $division->getValue();
            } else {
                //calue in percents -> let's calculate it
                /* @var Percent $division */
                $prop = (int)($maxSize * ($division->getValue() / 100));
                $this->calculatedSizes[$key] = $prop;
                $percentPropCount += $prop;
                $percentIndexes[] = $key;
            }
        }
        if (!empty($percentIndexes) && $percentPropCount > 0) {
            //only add reminder to percent-calculated sizes
            $this->calculatedSizes = $this->addReminder(
                $percentIndexes,
                $this->calculatedSizes,
                $maxSize - $percentPropCount
            );
        }
    }

    protected function addReminder(array $indices, array $numbers, $reminder)
    {
        //add reminder to smallest elements
        asort($numbers);
        foreach ($numbers as $k => $number) {
            if (!in_array($k, $indices)) {
                continue;
            }
            if ($reminder == 0) {
                break;
            }
            $numbers[$k]++;
            $reminder--;
        }
        ksort($numbers);
        return $numbers;
    }

    /**
     * Try to divide into equal pieces
     * Used if no divisions are set
     * @param int $count
     */
    protected function generateDivisions(int $count)
    {
        $this->generated = true;
        $dividedValue = (int)(100 / $count);
        $this->divisions = [];
        for ($i = 0; $i < $count; $i++) {
            $this->divisions[] = new Percent($dividedValue);
        }
        if (100 - ($dividedValue * $count) > 0) {
            $reminder = 100 - ($dividedValue * $count);
            /** @var Percent $last */
            $last = $this->divisions[count($this->divisions) - 1];
            $last->set($last->getValue() + $reminder);
        }
        $this->originalDivisions = $this->divisions;
    }

    /**
     * Activate/deactivate selected divisions (depends if component is active)
     * @return bool
     */
    private function fixInactiveDivisions(): bool
    {
        if($this->isValid() || $this->generated){ return false; }
        $children = ComponentFilter::rewriteKeys($this->parentComponent->getChildren(ComponentFilter::floating(true)));
        $this->divisions = [];
        foreach ($children as $key => $component) {
            if($component->isActive()){
                $this->divisions[] = clone($this->originalDivisions[$key]);
            }
        }
        return true;
    }

    /**
     * Invalidate layout to recalculate it
     * @param bool $flag
     * @return AbstractLayout
     */
    public function setValid(bool $flag): AbstractLayout
    {
        //clear generated sizes
        if(!$flag && $this->generated){
            $this->divisions = [];
            $this->calculatedSizes = [];
        }
        return parent::setValid($flag);
    }

    protected function correctPixelsForBorders()
    {
        $children = ComponentFilter::rewriteKeys($this->parentComponent->getChildren(
            ComponentFilter::floating(true), ComponentFilter::active()
        ));
        /** @var Component $child */
        foreach ($children as $index => $child) {
            $borderSize = $this->getComponentBorderSize($child);
            if ($borderSize > 0) {
                $division = $this->originalDivisions[$index];
                if ($division instanceof Pixel) {
                    $this->divisions[$index]->set($division->getValue() + $borderSize);
                }
            }else{
                $this->divisions[$index]->set($this->originalDivisions[$index]->getValue());
            }
        }
    }

    protected abstract function getComponentBorderSize(Component $c): int;

}