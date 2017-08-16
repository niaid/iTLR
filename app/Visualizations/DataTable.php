<?php namespace iTLR\Visualizations;

class DataTable extends Visualization
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
        $dataNo = count($this->_data);

        $labels = array_keys($this->_data[0]);

        $aoColumns = array();
        $nColNumber = -1;
        foreach ($labels as $label) {
            $keys[] = $label;
            $nColNumber++;
            array_push($aoColumns, array('title' => $label, 'data' => $label,  'targets'=> $nColNumber ));
        }

        // Add Ids, just in case we want them later
        /*$keys[] = 'id';

        for ($i = 0; $i < $count; $i++) {
          $data[$i][] = $i;
        }*/

        // Bring it all together
        $newArray = array();
        for ($j = 0; $j < $dataNo; $j++) {
            $d = array_combine($keys, $this->_data[$j]);
            $newArray[$j] = $d;
        }

        $newArray = array('columnDefs' => $aoColumns, 'aaData' => $newArray);
        // Print it out as JSON
        echo json_encode($newArray);
    }
}