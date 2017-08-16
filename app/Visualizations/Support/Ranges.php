<?php namespace iTLR\Visualizations\Support;

use iTLR\Experiment\Experiment;

class Ranges {

    /**
     * @var array
     */
    protected $ranges;

    protected $experimentSpecific;

    public function __construct($ranges, $experimentSpecific = false)
    {
        $this->experimentSpecific = $experimentSpecific;
        if($ranges == null)
        {
            $this->ranges = null;
        }
        else
        {
            $this->ranges = explode(',', $ranges);
        }
    }

    public function isNull()
    {
        return is_null($this->ranges);
    }

    public function getRange()
    {
        if($this->isNull())
        {
            return array();
        }

        return $this->ranges;
    }

    public function getRangesForExperiment(Experiment $experiment)
    {
        if($this->isNull())
        {
            return new Ranges(null);
        }


        $ranges = $this->ranges[$experiment->getOrder() * 2].','. $this->ranges[($experiment->getOrder() * 2)+ 1];
        return new Ranges($ranges, true);
    }



}