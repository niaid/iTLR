<?php

namespace iTLR\Upload;

class Network
{
    private $_mandatoryFields = array('Gene_A', 'Gene_B', 'Organism');
    private $_optionalFields = array('EntrezID_A', 'EntrezID_B', 'Experimental_System', 'Experimental_System_Type');

    private $_data;
    private $_headers;

    //Error Handling
    private $_errors = array();
    private $_warnings = array();
    private $_skippedRows = array();

    public function __construct($fileName)
    {
        $this->validateFile($fileName);
    }

    public function isValid()
    {
        return (count($this->_errors) == 0);
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getWarnings()
    {
        $this->_warnings = array();
        if (count($this->_skippedRows) > 0) {
            $this->_warnings[] = 'Skipped rows will be displayed below all the errors';
            $this->_warnings[] = 'Rows are skipped if they do not meet the mandatory fields requirement';
            $this->_warnings[] = 'To upload a skipped row, fix the row and re-upload it in a different file; otherwise, Duplicates will BE formed';
            $this->_warnings = array_merge($this->_warnings, $this->_skippedRows);
        }

        return $this->_warnings;
    }


    private function validateFile($fileName)
    {
        $result = File::handleUpload($fileName);
        if ($result['Valid'] != 1) {
            $this->_errors = $result['Error'];
            return;
        }

        $this->validateHeaders($result['File']['data'][0]);

        //print_modified_r($this->_headers);

        $this->validateData($result['File']['data']);

        $this->insertDataIntoDB();


    }

    private function insertDataIntoDB()
    {
        if (!$this->isValid()) return;

        global $db;

        $genes = array();
        for ($i = 0; $i < count($this->_data); $i++) {
            $genes[] = $this->_data[$i]['Gene_A'];
            $genes[] = $this->_data[$i]['Gene_B'];
        }

        $genes = array_unique($genes);

        $genes = "'" . implode("','", $genes) . "'";

        /*$req = $db->query('SELECT * FROM network WHERE Gene_A IN ('.$genes .') AND Gene_B IN ('.$genes.')');

        while($data = $req->fetch(PDO::FETCH_ASSOC)) {
            $geneRow = array($data['Gene_A'], $data['Gene_B'], $data['Organism']);
            //print_modified_r($data);

            $geneA = array_keys(array_column($this->_data, 'Gene_A'), $data['Gene_A']);
            $geneB = array_keys(array_column($this->_data, 'Gene_B'), $data['Gene_B']);
            $intersect = array_intersect($geneA, $geneB);

            echo 'Gene A'.$data['Gene_A'].'<br/>';
            echo 'Gene B'.$data['Gene_B'].'<br/>';
            echo 'Intersect<br/>';
            print_modified_r($intersect);

            for($a = 0; $a < count($intersect);$a++) {
                print_modified_r($this->_data[$intersect[$a]]);
            }


            $search = FALSE;

            if($search !== FALSE)
            {
                if($this->_data[$search]['EntrezID_A'] == $data['EntrezID_A']
                    && $this->_data[$search]['EntrezID_B'] == $data['EntrezID_B']
                    && $this->_data[$search]['Gene_A'] == $data['Gene_A']
                    && $this->_data[$search]['Gene_B'] == $data['Gene_B'])
                {
                    $this->_skippedRows[] = array_merge(array('Reason' => 'Already in database'), $this->_data[$search]);
                    unset($this->_data[$search]);
                }
            }
        }*/

        $this->_data = array_values($this->_data);

        $columns = array_keys($this->_headers);
        $columnQuery = '';
        $columnNo = count($columns);

        for ($i = 0; $i < $columnNo; $i++) {
            if ($i == 0) {
                $columnQuery .= '(' . $columns[$i];
            } else if ($i == $columnNo - 1) {
                $columnQuery .= ',' . $columns[$i] . ')';
            } else {
                $columnQuery .= ',' . $columns[$i];
            }
        }

        $valuesQuery = array();

        for ($i = 0; $i < count($this->_data); $i++) {
            //for($i = 0; $i < 5; $i++) {
            $valuesQuery[] = '("' . implode('","', $this->_data[$i]) . '")';
        }
        $valuesQuery = implode(',', $valuesQuery);

        //echo $columnQuery.'<br/>';
        //echo $valuesQuery;

        $db->query('INSERT INTO network ' . $columnQuery . ' VALUES' . $valuesQuery);
    }

    private function validateData($data)
    {
        if (count($this->_errors) > 0) return;

        $this->_skippedRows = array();
        $this->_data = array();

        for ($i = 1; $i < count($data); $i++) {
            $validRow = true;
            $row = array();

            foreach ($this->_mandatoryFields as $field) {
                if ($data[$i][$this->_headers[$field]] == '') {
                    $validRow = false;
                }
                $row[$field] = $data[$i][$this->_headers[$field]];
            }

            if ($validRow == false) {
                if (is_array($data[$i])) {
                    $skippedRow = array();
                    $columns = array_keys($this->_headers);

                    for ($a = 0; $a < count($this->_headers); $a++) {
                        $skippedRow[$columns[$a]] = $data[$i][$this->_headers[$columns[$a]]];
                    }
                } else {
                    $skippedRow = array('Empty (Most likely an empty line in file)');
                }
                $this->_skippedRows[] = array_merge(array('Line' => $i, 'Reason' => 'Mandatory Field Empty'), $skippedRow);
                continue;
            }

            foreach ($this->_optionalFields as $field) {
                if (isset($data[$i][$this->_headers[$field]])) {
                    $row[$field] = $data[$i][$this->_headers[$field]];
                } else {
                    $row[$field] = '';
                }
            }

            $this->_data[] = $row;
        }

        if (count($this->_data) == 0) {
            $this->_errors[] = (count($this->_skippedRows) > 0) ? 'All rows were skipped' : 'No data found';
        }

    }


    private function validateHeaders($header)
    {
        $this->_headers = array();
        $header = array_map('strtolower', $header);
        $mandatoryFieldsLowerCase = array_map('strtolower', $this->_mandatoryFields);

        if (count(array_diff($mandatoryFieldsLowerCase, $header)) > 0) {
            $difference = array_diff($mandatoryFieldsLowerCase, $header);
            $difference = array_keys($difference);

            for ($i = 0; $i < count($difference); $i++) {
                $this->_errors[] = 'Missing mandatory field ' . $this->_mandatoryFields[$difference[$i]];
            }

            return;
        }

        for ($i = 0; $i < count($this->_mandatoryFields); $i++) {
            for ($a = 0; $a < count($header); $a++) {
                if (strtolower($this->_mandatoryFields[$i]) == strtolower($header[$a])) {
                    $this->_headers[$this->_mandatoryFields[$i]] = $a;
                    break;
                }
            }
        }

        for ($i = 0; $i < count($this->_optionalFields); $i++) {
            $this->_headers[$this->_optionalFields[$i]] = '';

            for ($a = 0; $a < count($header); $a++) {
                if (strtolower($this->_optionalFields[$i]) == strtolower($header[$a])) {
                    $this->_headers[$this->_optionalFields[$i]] = $a;
                    break;
                }
            }
        }

    }
}