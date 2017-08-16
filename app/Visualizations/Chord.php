<?php namespace iTLR\Visualizations;

use iTLR\Experiment\Experiment;
use iTLR\Helpers\Prints;
use iTLR\Output\JSONRequest;
use iTLR\Visualizations\Support\Filter;
use iTLR\Visualizations\Support\Ranges;

class Chord extends Visualization
{

    protected function validate()
    {
        $this->_filter = new Filter(Filter::$union);

        if($_GET['id'] == 1)
        {
            $this->_ranges = new Ranges(null);
        }
    }

    protected function handle()
    {
        $experimentNo = $this->_experiments->count();
        $data = array();

        foreach ($this->_experiments->get() as $experiment)
        {
            $ranges = $this->_ranges->getRangesForExperiment($experiment);
            $data[] = $experiment->getData(array(Experiment::$gene), $ranges, $this->_operation)->fetchAll(\PDO::FETCH_COLUMN);
        }

        $matrix = array();
        for($i=0; $i< $experimentNo; $i++) {
            $dataTmp = array($data[$i]);
            for($j=0; $j< $experimentNo; $j++) {
                if($i != $j) {
                    $dataTmp[] = $data[$j];
                    $matrix[$i][$j] = count(array_intersect($data[$i],$data[$j]));
                }
            }
            $matrix[$i][$i] = count(call_user_func_array('array_diff', $dataTmp));

        }

        $this->_data = $matrix;
    }

    protected function outputData()
    {
        echo json_encode($this->_data);
    }
}