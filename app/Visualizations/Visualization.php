<?php namespace iTLR\Visualizations;

use iTLR\Experiment\Experiments;
use iTLR\Output\CSV;
use iTLR\Parameters\ExperimentParametersManagement;
use iTLR\Parameters\ParametersManagement;
use iTLR\Visualizations\Support\Filter;
use iTLR\Visualizations\Support\Operation;
use iTLR\Visualizations\Support\Ranges;

abstract class Visualization {


    protected $_data;

    /**
     * @var array
     */
    protected $_errors;

    /**
     * @var Experiments
     */
    protected $_experiments;

    /**
     * @var Operation
     */
    public $_operation;

    /**
     * @var Filter
     */
    public $_filter;

    /**
     * @var Ranges
     */
    public $_ranges;

    public function __construct($experiments)
    {
        $this->setUp();
        $this->_experiments = $experiments;

        if ($this->validate() == -1) {
            return;
        }

        $this->handle();

        $this->outputData();
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    protected function setUp()
    {
        if(isset($_GET['operation']))
        {
            $this->_operation       = new Operation($_GET['operation']);
        }
        else
        {
            $this->_operation       = new Operation(null);
        }

        if(isset($_GET['filter']))
        {
            $this->_filter          = new Filter($_GET['filter']);
        }
        else
        {
            $this->_filter          = new Filter(null);
        }

        if(isset($_GET['ranges']))
        {
            $this->_ranges          = new Ranges($_GET['ranges']);
        }
        else
        {
            $this->_ranges          = new Ranges(null);
        }
    }

    abstract protected function validate();

    abstract protected function handle();

    abstract protected function outputData();

    public static function output()
    {
        new static($_SESSION['Experiments']);
    }

}