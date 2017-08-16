<?php namespace iTLR\Upload;

use iTLR\Database\Database;
use \iTLR\Upload\Materials\UploadFile;
use \iTLR\Upload\Materials\InfoFile;
use \iTLR\Upload\Materials\DataFile;

class Experiments
{
    private $_infoFile = null;
    private $_dataFiles = array();

    protected $_errors;
    protected $_skippedRows;
    protected $_warnings;


    public function __construct($dataName, $infoName = null)
    {
        if (!is_null($infoName)) {
            $this->_infoFile = new InfoFile($infoName);
            ob_flush(); flush();
        }

        for ($i = 0; $i < count($_FILES[$dataName]['name']); $i++) {
            $this->_dataFiles[] = new DataFile($dataName, $i);
            ob_flush(); flush();
        }

        $this->retrieveFileErrors();
        $this->retrieveSkippedRows();
        $this->retrieveWarnings();

        $this->uploadExperiments();
    }

    private function retrieveFileErrors() {
        if(!is_null($this->_infoFile)) {
            $this->_errors = $this->_infoFile->getErrors();
        }

        for($i = 0; $i < count($this->_dataFiles); $i++) {
            $this->_errors = array_merge($this->_errors, $this->_dataFiles[$i]->getErrors());
        }
    }

    private function retrieveSkippedRows() {
        if(!is_null($this->_infoFile)) {
            $this->_skippedRows = $this->_infoFile->getSkippedRows();
        }

        for($i = 0; $i < count($this->_dataFiles); $i++) {
            $this->_skippedRows = array_merge($this->_skippedRows, $this->_dataFiles[$i]->getSkippedRows());
        }
    }

    private function retrieveWarnings() {
        if(!is_null($this->_infoFile)) {
            $this->_warnings = $this->_infoFile->getWarnings();
        }

        for($i = 0; $i < count($this->_dataFiles); $i++) {
            $this->_warnings = array_merge($this->_warnings, $this->_dataFiles[$i]->getWarnings());
        }
    }

    public function getErrors() {
        return $this->_errors;
    }

    public function isValid() {
        return (count($this->_errors) == 0);
    }

    public function getWarnings() {
        return $this->_warnings;
    }

    public function getSkippedRows() {
        return $this->_skippedRows;
    }

    private function uploadExperiments() {
        for($i = 0; $i < count($this->_dataFiles); $i++) {
            $dataFile = $this->_dataFiles[$i];
            if(!$dataFile->isValid()) {
                continue;
            }

            $dataLinks = $dataFile->getDataLinks();
            //print_modified_r($dataLinks);

            echo '<br/><br/>File:'.$dataFile->getName().'<br/><br/>';

            for($a = 0; $a < count($dataLinks); $a++) {
                echo '------------------------------<br/>';
                echo 'Data Link:'.$dataLinks[$a].'<br/>';
                $startTime = microtime(true);
                $this->uploadExperiment($dataLinks[$a], $i);
                $endTime = microtime(true) - $startTime;
                echo 'Exp Time:'.$endTime.'<br/>';
                ob_flush(); flush();
            }
        }
    }

    private function uploadExperiment($experimentLink, $fileIndex) {
        $row = $this->_infoFile->findExperimentLinkRow($experimentLink);
        if($row == FALSE) {
            $this->_errors[] = array('Reason' => 'Could not find:'.$experimentLink.' in the info file',
                                     'Possibilities' => array('Data_Link Column does not contain this value',
                                                              'Mandatory Fields were not set for that row'));
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();
            $expId = $this->_infoFile->insertIntoExperimentTable($row);
            $this->_infoFile->insertStimulation($row, $expId);
            echo 'Exp ID:'.$expId.'<br/>';
            $platform = $this->_dataFiles[$fileIndex]->insertData($experimentLink, $row);
            $this->linkDataWithExperiment($platform, $expId);
            echo 'Platform Genes Number:'.count($platform).'<br/>';
            $db->commit();
        } catch(\Exception $e) {
            $db->rollBack();
            $this->_errors[] = $e->getMessage();
        }
    }

    private function linkDataWithExperiment($platform, $expId) {
        $db = Database::getInstance();

        $arrayValues = array();
        $arrayQuery  = array();

        for($i = 0; $i < count($platform); $i++) {
            $arrayValues[] = $expId;
            $arrayValues[] = $platform[$i]['ID'];
            $arrayValues[] = $platform[$i]['Value'];
            $arrayQuery[]  = '(?,?,?)';
        }

        $req = $db->prepare('INSERT INTO experiment_gene (ExperimentID, GeneID, Value) VALUES'.implode(',', $arrayQuery));
        $req->execute($arrayValues);
    }
}
