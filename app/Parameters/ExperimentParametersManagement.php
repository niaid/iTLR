<?php

namespace iTLR\Parameters;

use iTLR\Experiment\Experiment;

class ExperimentParametersManagement extends ParametersManagement
{

    public function getJSON($id)
    {
        if(!isset($this->_parameters[$id])) {
            return array();
        }

        $json['Buttons']['New'] = $this->_parameters[$id]->newButtons();
        $json['Buttons']['Available'] = $this->_parameters[$id]->available();
        $json['Buttons']['Selected'] = $this->_parameters[$id]->selected();
        $json['Display']['Status'] = $this->_parameters[$id]->displayStatus();
        $json['Display']['Completed'] = $this->_parameters[$id]->completed();

        //Count of each array since JavaScript cannot count the number of elements
        $json['Buttons']['New']['count'] = count($json['Buttons']['New']);
        $json['Buttons']['Available']['count'] = count($json['Buttons']['Available']);
        $json['Buttons']['Selected']['count'] = count($json['Buttons']['Selected']);

        return $json;
    }
}


?>