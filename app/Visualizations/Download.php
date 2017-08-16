<?php namespace iTLR\Visualizations;

use iTLR\Output\CSV;

class Download extends Visualization
{
    protected function validate()
    {
        return 0;
    }

    protected function handle()
    {
        $this->_data = $this->_filter->handle($this->_experiments, $this->_operation, $this->_ranges)->getData();
    }

    protected function outputData()
    {
        $csv = new CSV($this->_data);
        $csv->output();
    }
}