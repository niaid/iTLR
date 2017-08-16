<?php namespace iTLR\Upload\Materials;

use iTLR\Database\Database;

class DataFile extends UploadFile
{
    protected $_mandatoryFields = array('GeneSymbol');
    protected $_optionalFields = array('AltID');
    protected $_orginalOptionalFields = array('AltID');

    protected $_name;
    protected $_header;
    protected $_data;
    protected $_originalData;

    private $roundValue = 5;

    protected $_errors = array();
    protected $_warnings = array();
    protected $_skippedRows = array();

    public function __construct($name, $index)
    {
        parent::__construct($name, $index, true);
    }

    public function insertData($experimentLink, $experimentInfo)
    {
        $values = $this->getColumn($experimentLink, $experimentInfo['Organism']);
        $platform = $this->getPlatform($values);

        return $platform;
    }

    private function getPlatform($values) {
        $db = Database::getInstance();

        $platform = $values;
        $platform = array_values($platform);
        $arrayValues = array();
        $arrayQuery  = array();

        $selectQuery = $db->prepare('SELECT id FROM platform WHERE Gene = :Gene AND Alias = :Alias');
        $insertQuery = $db->prepare('INSERT INTO platform (Gene, Alias) VALUES(:Gene, :Alias)');

        for($i = 0; $i < count($platform); $i++) {
            $selectQuery->execute(array('Gene' => $platform[$i]['GeneSymbol'], 'Alias' => $platform[$i]['AltID']));
            $data = $selectQuery->fetch();

            if($data != FALSE) {
                $platform[$i]['ID'] = $data['id'];
            } else {
                $insertQuery->execute(array('Gene' => $platform[$i]['GeneSymbol'], 'Alias' => $platform[$i]['AltID']));
                $platformId = $db->lastInsertId();
                $platform[$i]['ID'] = $platformId;
            }
        }

        return $platform;
    }

    private function isValidValue($value) {
        return is_numeric($value);
    }

    private function getColumn($experimentLink, $organism)
    {

        $values = array();
        for($i = 0; $i < count($this->_data); $i++) {
            if($this->isValidValue($this->_data[$i][$experimentLink])) {
                $geneSymbol = $this->_data[$i]['GeneSymbol'];
                if(ucfirst(strtolower($organism)) == 'Mouse') {
                    $geneSymbol = ucfirst(strtolower($geneSymbol));
                } else if(ucfirst(strtolower($organism)) == 'Human') {
                    $geneSymbol = strtoupper($geneSymbol);
                }


                $values[] = array('GeneSymbol' => $geneSymbol,
                                  'AltID'      => $this->_data[$i]['AltID'],
                                  'Value'      => round($this->_data[$i][$experimentLink], $this->roundValue));
            }
        }

        return $values;
    }

    public function getDataLinks()
    {
        return array_values(array_diff($this->_optionalFields, array_merge($this->_mandatoryFields, $this->_orginalOptionalFields)));
    }
}
