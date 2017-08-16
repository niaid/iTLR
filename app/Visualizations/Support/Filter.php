<?php namespace iTLR\Visualizations\Support;


use iTLR\Experiment\Experiments;
use iTLR\Filters\Intersection;
use iTLR\Filters\Union;

/**
 *  Determines if the method is either intersection or union of genes from multiple experiments
 *
 * Safe against modification as default will be intersection.
 *
 * Class Filter
 * @package iTLR\VisualizationSupport
 */
class Filter
{

    protected $_filter;

    public static $intersection = 'intersection';
    public static $union        = 'union';

    public function __construct($filter)
    {
        $this->_filter = $this->validate($filter);
    }

    public function validate($filter)
    {
        switch ($filter)
        {
            case self::$intersection:
                return self::$intersection;
            case self::$union:
                return self::$union;
            default:
                return self::$intersection;
        }
    }

    public function handle(Experiments $experiments, Operation $operation, Ranges $ranges)
    {
        if($this->_filter == self::$intersection)
        {
            return new Intersection($experiments, $operation, $ranges);
        }
        else
        {
            return new Union($experiments, $operation, $ranges);
        }

    }




}