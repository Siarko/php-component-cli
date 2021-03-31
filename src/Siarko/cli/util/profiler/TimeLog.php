<?php


namespace Siarko\cli\util\profiler;


class TimeLog
{

    private array $logs = [];
    private $startTime;
    private $endTime;

    public function __construct()
    {
        $this->new();
    }

    public function new(){
        $this->startTime = hrtime(true);
    }

    public function end(){
        $this->endTime = hrtime(true);
        $this->logs[] = $this->endTime-$this->startTime;
    }

    public function getSampleCount(): int
    {
        return count($this->logs);
    }

    /**
     * @param TimeFactor $timeFactor
     * @param int $precision
     * @return float
     */
    public function getMinTime(TimeFactor $timeFactor, int $precision = 3): float
    {
        if(empty($this->logs)){ return 0; }
        $min = $this->logs[0];
        foreach ($this->logs as $log) {
            if($log < $min){
                $min = $log;
            }
        }
        return round($min/$timeFactor->getValue(), $precision);
    }

    /**
     * @param TimeFactor $timeFactor
     * @param int $precision
     * @return float|int
     */
    public function getMaxTime(TimeFactor $timeFactor, int $precision = 3){
        if(empty($this->logs)){ return 0; }
        $min = $this->logs[0];
        foreach ($this->logs as $log) {
            if($log > $min){
                $min = $log;
            }
        }
        return round($min/$timeFactor->getValue(), $precision);
    }


    /**
     * @param TimeFactor $timeFactor
     * @param int $precision
     * @return float|int
     */
    public function getAvgTime(TimeFactor $timeFactor, int $precision = 3){
        $divisions = count($this->logs);
        if($divisions == 0){
            return 0;
        }
        $avg = array_sum($this->logs)/$divisions;
        return round($avg/$timeFactor->getValue(), $precision);
    }

    /**
     * @param TimeFactor $timeFactor
     * @param int $precision
     * @return float
     */
    public function getSumTime(TimeFactor $timeFactor, int $precision = 3): float
    {
        return round(array_sum($this->logs)/$timeFactor->getValue(), $precision);
    }

}