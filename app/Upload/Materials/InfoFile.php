<?php 

namespace iTLR\Upload\Materials;

use iTLR\Database\Database;

class InfoFile extends UploadFile {
    protected $_mandatoryFields = array('Name', 'Data_Link', 'DataType', 'CellType', 'Organism', 'Stimulation', 'Concentration', 'TimePoint', 'Experimentalist', 'Replicate', 'Protocol', 'Strain');
    protected $_optionalFields = array('Readout', 'Receptor', 'Platform', 'Citation', 'Gender_Age_Other', 'GSM_Number', 'GSE_Number');
    private $_dataLink = 'Data_Link';
    private $_experimentTableDiff = array('Data_Link', 'Stimulation', 'Concentration');

    protected $_name;
    protected $_header;
    protected $_data;
    protected $_originalData;

    //ERROR HANDLING
    protected $_errors = array();
    protected $_warnings = array();
    protected $_skippedRows = array();


    public function findExperimentLinkRow($experimentLink) {
        $experimentLink = strtolower($experimentLink);

        for($i = 0; $i < count($this->_data); $i++) {
            if(strtolower($this->_data[$i][$this->_dataLink]) == $experimentLink) {
                return $this->_data[$i];
            }
        }

        return FALSE;
    }

    public function insertIntoExperimentTable($row) {
        $db = Database::getInstance();

        $experimentTable = array_merge(array_diff($this->_mandatoryFields, $this->_experimentTableDiff), $this->_optionalFields);
        $experimentValues = array();
        $experimentHolders = array();
        for($i = 0; $i < count($experimentTable); $i++) {
            $experimentValues[]  = $this->sanitizeData($row, $experimentTable[$i]);
            $experimentHolders[] = '?';
        }

        //Experiment Table
        $req = $db->prepare('INSERT INTO experiments('.implode(',', $experimentTable).') VALUES('.implode(',', $experimentHolders).')');
        $req->execute($experimentValues);

        return $db->lastInsertId();
    }



    public function insertStimulation($row, $expID) {
        $db = Database::getInstance();

        $values = array();
        $valuesQuery = array();

        $insertIDs = array();
        $stimulation = $this->breakString($this->sanitizeData($row, 'Stimulation'));
        $concentration = $this->breakString($this->sanitizeData($row, 'Concentration'));

        //print_modified_r($stimulation);
        //print_modified_r($concentration);

        if(count($stimulation) != count($concentration)) {
            throw new \Exception('The number of stimulation and concentration do not match');
        }

        //print_modified_r($stimulation);
        //print_modified_r($concentration);


        $req = $db->query('SELECT * FROM stimulation');

        $stimulationNo = count($stimulation);
        while($data = $req->fetch()) {
            for($i = 0; $i < $stimulationNo; $i++) {
                if(isset($stimulation[$i]) &&
                        $data['Stimulus'] == $stimulation[$i] && $data['Concentration'] == $concentration[$i]) {
                    $insertIDs[] = $data['ID'];
                    unset($stimulation[$i]);
                    unset($concentration[$i]);
                    break;
                }
            }
        }

        if(count($stimulation) > 0) {
            $stimulation = array_values($stimulation);
            $concentration = array_values($concentration);

            for ($i = 0; $i < count($stimulation); $i++) {
                $values[] = $stimulation[$i];
                $values[] = $concentration[$i];
                $valuesQuery[] = '(?,?)';
            }

            $req = $db->prepare('INSERT INTO stimulation(Stimulus, Concentration) VALUES' . implode(',', $valuesQuery));
            $req->execute($values);

            $lastInsertID = $db->lastInsertId();

            for ($i = $lastInsertID; $i < $lastInsertID + count($valuesQuery); $i++) {
                $insertIDs[] = $i;
            }
        }

        $valuesQuery = array();
        $values      = array();
        for($i = 0; $i < count($insertIDs); $i++) {
            $valuesQuery[] = '(?,?)';
            $values[] = $expID;
            $values[] = $insertIDs[$i];
        }

        $req = $db->prepare('INSERT INTO experiment_stimulation(ExperimentID, StimulationID) VALUES'.implode(',', $valuesQuery));
        $req->execute($values);

    }


}
