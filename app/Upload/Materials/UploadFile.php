<?php namespace iTLR\Upload\Materials;

use iTLR\Helpers\Prints;

abstract class UploadFile {
    protected $_name;
    protected $_mandatoryFields;
    protected $_optionalFields;

    protected $_stringSplitter = array('_+_', '_&_', '_,_', '+', '&', ','); //for splitters
    private $_replaceCharacters = array(' ' => '_', '/' => ':');


    protected $_header;
    protected $_data;
    protected $_originalData;

    //Error Handling
    protected $_errors = array();
    protected $_warnings = array();
    protected $_skippedRows = array();

    public function getErrors() {
        return $this->_errors;
    }

    public function getSkippedRows() {
        return $this->_skippedRows;
    }

    public function getWarnings() {
        return $this->_warnings;
    }
    public function isValid() {
        return (count($this->_errors) == 0);
    }

    public function getName() {
        return $this->_name;
    }


    public function __construct($name, $index = NULL, $extra = false)
    {
        $this->_name = is_null($index) ? $_FILES[$name]['name'] : $_FILES[$name]['name'][$index];

        $this->validateFile($name, $index, $extra);
    }


    protected function validateFile($name, $index = NULL, $extra = false)
    {
        if (is_null($name)) {
            $this->_errors[] = 'File ' . htmlspecialchars($name) . ' Null';

            return;
        }

        $file = File::handleUpload($name, $index);

        if ($file['Valid'] == false) {
            $this->_errors[] = 'Unsuccessfully Uploaded ' . htmlspecialchars($name) . ' File:' . $this->_name;
            $this->_errors[] = $file['Error'];

            return;
        }

        if($extra == true) {
            $this->_optionalFields = array_values(array_diff(array_merge($file['File']['data'][0], $this->_optionalFields), $this->_mandatoryFields));
        }


        $this->validateHeaders($file['File']['data'][0]);

        if (count($this->_errors) == 0) {
            $this->_originalData = $file['File']['data'];
            $this->validateData($file['File']['data']);
        }

        //print_modified_r($this->_errors);
        //print_modified_r($this->_header);
        //print_modified_r($this->_data);
        //print_modified_r($this->_skippedRows);

    }

    private function validateData($data)
    {
        if (count($this->_errors) > 0) return;

        for ($i = 1; $i < count($data); $i++) {
            $validRow = array();
            $row = array();
            $skippedRow = array();



            foreach ($this->_mandatoryFields as $field) {
                if ($data[$i][$this->_header[$field]] == '' && !is_int($data[$i][$this->_header[$field]])) {
                    $validRow[] = $field;
                }
                $row[$field] = $data[$i][$this->_header[$field]];
            }

            if (count($validRow) > 0) {
                if (is_array($data[$i])) {
                    $columns = array_keys($this->_header);
                    for ($a = 0; $a < count($columns); $a++) {
                        if(isset($data[$i][$this->_header[$columns[$a]]])) {
                            $skippedRow[$columns[$a]] = $data[$i][$this->_header[$columns[$a]]];
                        }
                    }
                } else {
                    $skippedRow = array('Empty' => 'Empty (Most likely an empty line in file)');
                }
                $skippedRow = array_merge(array('Line' => $i+1, 'Reason' => 'Mandatory Field Empty', $skippedRow, 'Missing Values' => $validRow));

                $this->_skippedRows[] = array_merge(array('File' => $this->_name), $skippedRow);

                continue;
            }

            foreach ($this->_optionalFields as $field) {
                if (isset($data[$i][$this->_header[$field]])) {
                    $row[$field] = $data[$i][$this->_header[$field]];
                } else {
                    $row[$field] = '';
                }
            }

            $this->_data[] = $row;
        }

        if (count($this->_data) == 0) {
            if (count($this->_skippedRows) > 0) {
                $this->_errors[] = 'All rows were skipped in info';
            } else {
                $this->_errors[] = 'No data found';
            }
        }
    }


    private function validateHeaders($header)
    {
        $headerTmp = array();

        //Prints::array($header);

        $header = array_map('strtolower', $header);
        $mandatoryFieldsLowerCase = array_map('strtolower', $this->_mandatoryFields);

        $difference = array_diff($mandatoryFieldsLowerCase, $header);
        if (count($difference) > 0) {
            $difference = array_keys($difference);

            for ($i = 0; $i < count($difference); $i++) {
                $this->_errors[] = 'Missing mandatory field ' . $this->_mandatoryFields[$difference[$i]];
            }

            return;
        }

        for ($i = 0; $i < count($this->_mandatoryFields); $i++) {
            for ($a = 0; $a < count($header); $a++) {
                if ($mandatoryFieldsLowerCase[$i] == $header[$a]) {
                    $headerTmp[$this->_mandatoryFields[$i]] = $a;
                    break;
                }
            }
        }

        for ($i = 0; $i < count($this->_optionalFields); $i++) {
            $headerTmp[$this->_optionalFields[$i]] = '';

            for ($a = 0; $a < count($header); $a++) {
                if (strtolower($this->_optionalFields[$i]) == $header[$a]) {
                    $headerTmp[$this->_optionalFields[$i]] = $a;
                    break;
                }
            }
        }
        $this->_header = $headerTmp;
        //print_modified_r($this->_header);
    }


    protected function breakString($string) {
        $string = trim($string);

        for($i = 0; $i < count($this->_stringSplitter); $i++) {
            if(count(explode($this->_stringSplitter[$i], $string)) > 1) {
                return explode($this->_stringSplitter[$i], $string);
            }
        }

        return array($string);
    }

    protected function sanitizeData($row, $name) {
        $value = $row[$name];

        if($name == 'CellType') {
            $value =  $row['Organism'] . ' ' . $row['CellType'];
        }

        if(in_array($name, $this->_mandatoryFields) && $name != 'Protocol') {
            $illegalCharacters = array_keys($this->_replaceCharacters);
            for ($i = 0; $i < count($this->_replaceCharacters); $i++) {
                $value = str_replace($illegalCharacters[$i], $this->_replaceCharacters[$illegalCharacters[$i]], $value);
            }
        }

        return trim($value);
    }
}
