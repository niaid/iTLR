<?php namespace iTLR\Visualizations;

use iTLR\Filters\Intersection;
use iTLR\Output\CSV;
use iTLR\Visualizations\Support\Filter;
use iTLR\Visualizations\Support\Ranges;

class ScatterPlot extends Visualization {

    /**
     * @return array
     */
    protected function handle()
    {
        $this->_data = $this->_filter->handle($this->_experiments, $this->_operation, $this->_ranges)->getData();
    }

    protected function outputData()
    {
        $csv = new CSV($this->_data);
        $csv->output();
    }

    /**
     * Validates the GET requests
     * @return int
     */
    protected function validate()
    {
        if($this->_operation == null)
        {
            $this->_errors[] = 'Operation must be selected';
            return -1;
        }

        $this->_filter = new Filter(Filter::$intersection);
        $this->_ranges = new Ranges(null);

        return 0;
    }



}