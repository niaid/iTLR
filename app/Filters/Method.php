<?php namespace iTLR\Filters;

use iTLR\Experiment\Experiments;
use iTLR\Visualizations\Support\Operation;
use iTLR\Visualizations\Support\Ranges;

abstract class Method
{
    protected $_data;

    /**
     * @var Experiments
     */
    protected $_experiments;

    /**
     * @var Operation
     */
    protected $_operation;

    /**
     * @var Ranges
     */
    protected $_ranges;


    public function __construct(Experiments $experiments, Operation $operation, Ranges $ranges)
    {
        $this->_experiments = $experiments;
        $this->_operation = $operation;
        $this->_ranges = $ranges;

        $this->handle();
    }

    abstract protected function handle();

    abstract public function getData();






}