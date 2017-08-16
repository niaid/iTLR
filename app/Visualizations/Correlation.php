<?php namespace iTLR\Visualizations;

use iTLR\Experiment\Experiment;
use iTLR\Experiment\Experiments;
use iTLR\Visualizations\Support\Filter;
use iTLR\Visualizations\Support\Ranges;

class Correlation extends Visualization
{
    protected $correlation;

    protected function validate()
    {
        $this->_filter = new Filter(Filter::$intersection);
        $this->_ranges  = new Ranges(null);
    }

    protected function handle()
    {
        $experimentNo = $this->_experiments->count();
        $experiments  = $this->_experiments->get();
        $values = $this->_filter->handle($this->_experiments, $this->_operation, $this->_ranges)->getData();


        $data = array();
        for($i = 1; $i < $experimentNo + 1; $i++)
        {
            $data[] = array_column($values, 'Data'.$experiments[$i - 1]->getTabNo());
        }



        $correlation = array();
        for ($i = 0; $i < $experimentNo; $i++) {
            for ($a = $i + 1; $a < $experimentNo; $a++) {
                $correlation[$i][$a] = round(\stats_stat_correlation($data[$i], $data[$a]), 4);
            }
        }

        $this->_data = $correlation;
    }

    protected function outputData()
    {
        $this->_data['Status'] = 1;
        echo json_encode($this->_data);
    }
}