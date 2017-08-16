<?php namespace iTLR\Visualizations;

use iTLR\Database\Database;
use iTLR\Experiment\Experiments;
use iTLR\Helpers\Prints;
use iTLR\Visualizations\Support\NodeImage;

class Pathway extends Visualization
{

    protected $_pathwayFile;

    protected static $_pathwayStoragePath = '../storage/Pathway/';

    private static $delimiters = array(',', '/', ';', ', ', '/ ');

    protected function validate()
    {
        if(!isset($_GET['pathwayOptions']) || $_GET['pathwayOptions'] == '')
        {
            return -1;
        }

        $db = Database::getInstance();
        $req = $db->prepare('SELECT 1 FROM pathway WHERE Pathway_ID = :Pathway');
        $req->execute(array('Pathway' => $_GET['pathwayOptions']));
        $data = $req->fetch();

        if(!isset($data['1']) || $data['1'] != '1')
        {
            return -1;
        }

        $this->_pathwayFile = $_GET['pathwayOptions'];
        return 0;
    }

    protected function handle()
    {
        $xml = $this->loadXML();

        $debugMode = array();

        $result = array('Valid' => true);
        $fileName = self::breakString($this->_pathwayFile, true);
        // 1. Retrieve respected pathway xml file
        $pathwayFile = self::$_pathwayStoragePath . $this->_pathwayFile . '.xml';

        if (file_exists($pathwayFile)) {
            $xml = simplexml_load_file($pathwayFile);
        } else {
            $result['Valid'] = false;
            $result['Message'] = 'Error: Unable to open file';

            error_log('Unable to open pathway file: '.$pathwayFile);

            echo json_encode($result);
            exit(); //EXIT
        }

        //print_r($this->_experiments);

        // 2. Retrieve all experiment data from database
        $data = $this->_filter->handle($this->_experiments, $this->_operation, $this->_ranges)->getData();

        if(in_array(3, $debugMode)) {
            print_r($data);
        }
        //print_r($data);

        // 3. Cycle through each element in xml file and add corresponding attributes
        $entryI = 0;
        $imageCreationData = array();
        $valueRange = array('Min', 'Max');

        if(in_array(1, $debugMode)) {
            //print the xml structure - make sure to view source in the web browser to see the results
            for ($i = 0; $i < 10; $i++) {
                print_r($xml->entry[$i]);
            }
        }

        //print_r($data);

        //NODES
        while ($entryI < $xml->entry->count()) {
            $entry = $xml->entry[$entryI];
            $type  = $entry->attributes()->type[0];

            if(($type != 'map' && $type != 'gene') || !isset($xml->entry[$entryI]->graphics->attributes()->name[0])) {
                //if the type is something else than useful in this pathway
                //or if there is no name attribute - no point in showing nothing
                $entryI++;
                continue;
            }

            //initialization of variables for each map or gene
            $name = strval($xml->entry[$entryI]->graphics->attributes()->name[0]);
            $geneImageData = array('DataValues' => array());
            $typeData = array('data' => array('shape'   => 'rectangle',
                'id'      => strval($xml->entry[$entryI]->attributes()->id[0]),
                'href'    => strval($xml->entry[$entryI]->attributes()->link[0]),
                'name'    => wordwrap(strval(self::breakString($xml->entry[$entryI]->graphics->attributes()->name[0], true)), intval(($xml->entry[$entryI]->graphics->attributes()->width[0]) / 8 + 3), "\n"),
                'width'   => intval($xml->entry[$entryI]->graphics->attributes()->width[0]),
                'height'  => intval($xml->entry[$entryI]->graphics->attributes()->height[0]),
                'nodeType'=> 'normal',
                'fontSize'=> '9px',
                'fontWeight' => 'normal',
                'opacity' => 1),
                'position' => array('x' => intval($xml->entry[$entryI]->graphics->attributes()->x[0]),
                    'y' => intval($xml->entry[$entryI]->graphics->attributes()->y[0])));

            //DEBUG 4
            echo (in_array(4, $debugMode)) ? 'Name: '.$name.' Type:'.$type.'<br/>'.PHP_EOL : '';

            //if map or gene
            if($type == "map") {
                if (strpos($name, "TITLE") !== FALSE) {
                    $typeData['data']['shape'] = 'roundrectangle';
                    $typeData['data']['fontSize'] = '14px';
                    $typeData['data']['fontWeight'] = 'bold';
                }
            } else if($type == 'gene') {
                $isGeneInDataPool = self::findGeneIndex($name, $data);
                //echo $isGeneInDataPool.'<br/>';
                $experiments = $this->_experiments->get();

                if($isGeneInDataPool !== false) {
                    $typeData['data']['name'] = wordwrap($isGeneInDataPool['geneName'], intval(($xml->entry[$entryI]->graphics->attributes()->width[0]) / 8 + 3), "\n");
                    for($i = 0; $i < count($data[$isGeneInDataPool['i']]) - 1; $i++) {
                        $geneImageData['DataValues'][] = $data[$isGeneInDataPool['i']]['Data'.$experiments[$i]->getTabNo()];
                    }
                }
            }

            $imageCreationData[] = $geneImageData;

            $result['data']['nodes'][] = $typeData;

            echo (in_array(4, $debugMode)) ? PHP_EOL : '';
            $entryI++;
        }


        //EDGES
        for ($r = 0; $r < $xml->relation->count(); $r++) {
            //echo $r;
            $result['data']['edges'][$r]['data']['source'] = strval($xml->relation[$r]->attributes()->entry1[0]);
            $result['data']['edges'][$r]['data']['target'] = strval($xml->relation[$r]->attributes()->entry2[0]);
        }

        //print_r($imageCreationData);

        //BACKGROUND IMAGES
        $result = self::processBackgroundImages($imageCreationData, $result, $fileName);

        if(in_array(2, $debugMode)) {
            Prints::array($result);
            Prints::array($imageCreationData);
        }

        $this->_data = $result;
    }

    protected function loadXML()
    {
        $pathwayFile = self::$_pathwayStoragePath . $this->_pathwayFile . '.xml';

        if (file_exists($pathwayFile)) {
            $xml = simplexml_load_file($pathwayFile);
        } else {
            exit('Failed to open ' . $pathwayFile);
        }

        return $xml;
    }

    protected function outputData()
    {
        echo json_encode($this->_data);
    }

    /**
     * @param $string - The string to break
     * @param bool|true $first - if the string can be broken into pieces, do you want it to return the first piece only?
     * @param array $delimiters - The delimiters to use as array, if blank result to default delimiters
     * @return array
     */
    private static function breakString($string, $first = false, array $delimiters = array('')) {
        $delimiters = ($delimiters[0] == '') ? static::$delimiters : $delimiters;

        foreach($delimiters as $delimiter) {
            $pieces = explode($delimiter, $string);

            if(count($pieces) > 1) {
                return ($first == true) ? $pieces[0] : $pieces;
            }
        }
        return ($first == true) ? $string : array($string);
    }

    private static function processBackgroundImages($imageCreationData, $result, $fileName) {
        $debugMode = array(0);

        echo (in_array(1, $debugMode)) ? count($result['data']['nodes']).':'.count($imageCreationData) : '';
        if(count($result['data']['nodes']) != count($imageCreationData) || count($imageCreationData) == 0) { return false; }

        $allValues = array();
        $totalHeight = 0;
        $maxWidth = $result['data']['nodes'][0]['data']['width'];
        $subSectionNo = 1;

        for($i = 0; $i < count($imageCreationData); $i++) {
            $totalHeight += $result['data']['nodes'][$i]['data']['height'];
            $maxWidth = ($result['data']['nodes'][$i]['data']['width'] > $maxWidth) ? $result['data']['nodes'][$i]['data']['width'] : $maxWidth;

            //subsectionNo
            $subSectionNoTmp = count($imageCreationData[$i]['DataValues']);
            $subSectionNo = ($subSectionNo < $subSectionNoTmp) ? $subSectionNoTmp : $subSectionNo;

            for($a = 0; $a < $subSectionNo; $a++) {
                if(isset($imageCreationData[$i]['DataValues'][$a]) && is_numeric($imageCreationData[$i]['DataValues'][$a])) {
                    $allValues[] = $imageCreationData[$i]['DataValues'][$a];
                }
            }
        }

        if(in_array(2, $debugMode)) {
            echo 'count:'.count($imageCreationData).'<br/>';
            echo 'max:'.max($allValues).'<br/>';
            echo 'min:'.min($allValues).'<br/>';
            echo 'max width:'.$maxWidth.'<br/>';
            echo 'totalHeight:'.$totalHeight.'<br/>';
            echo 'Number of sub sections:'.$subSectionNo;
        }

        $max = (count($allValues) > 0) ? max($allValues) : 0;
        $min = (count($allValues) > 0) ? min($allValues) : 0;

        $image = new NodeImage(count($imageCreationData),
            $max,
            $min,
            $maxWidth, $totalHeight, $subSectionNo);

        $totalHeight = 0;
        for($i = 0; $i < count($imageCreationData); $i++) {

            $image->addNodeToImage($imageCreationData[$i]['DataValues'],
                $result['data']['nodes'][$i]['data']['width'],
                $result['data']['nodes'][$i]['data']['height']);
            $result['data']['nodes'][$i]['data']['y'] = '-'.$totalHeight.'px';
            $totalHeight += $result['data']['nodes'][$i]['data']['height'];
        }

        $fileContents = $image->getImage();
        $_SESSION['Pathway'][$fileName]['Image'] = $fileContents;
        $result['quantile']['up'] = round($max, 4);
        $result['quantile']['down'] = round($min, 4);
        return $result;
    }

    /**
     * Finds the index of the gene in the geneData array
     * @param $gene
     * @param $geneData
     * @return bool|int|mixed|string
     */
    private static function findGeneIndex($gene, $geneData) {
        $debugMode = array(0);

        $genes = self::breakString($gene);

        //print_r($geneData);

        (in_array(1, $debugMode)) ? print_r($genes) : null;
        for($i = 0; $i < count($genes); $i++) {
            //TODO: Temporary fix
            $geneIndex = self::findGeneNameInData(ucfirst(strtolower($genes[$i])), $geneData);
            if($geneIndex != false) {
                return array('i' => $geneIndex, 'geneName' => $genes[$i]);
            }
        }

        return false;
    }

    public static function findGeneNameInData($geneName, $data) {
        $debugMode = array(0);
        if(in_array(1, $debugMode)) {
            echo 'Gene Name: ' . $geneName . PHP_EOL;
            print_r(array_column($data, 'Gene'));
        }
        if(in_array(2, $debugMode)) {
            echo array_search($geneName, array_column($data, 'Gene')).'<br/>'.PHP_EOL;
        }

        return array_search($geneName, array_column($data, 'Gene'));
    }

    public static function retrieve(Experiments $experiments)
    {
        $db = Database::getInstance();
        $organism = self::convertOrganismToDB($experiments->get()[0]->get('organism'));
        $result = array();
        $count = 0;

        $req = $db->query('SELECT Pathway_ID, Pathway_Name FROM pathway WHERE Pathway_ID LIKE "'.$organism.'%"');

        while ($data = $req->fetch()) {

            $result['Pathway'][$count]['id'] = $data['Pathway_ID'];
            $result['Pathway'][$count]['text'] = $data['Pathway_Name'];

            $count++;
        }
        $result['count'] = $count;

        return $result;
    }

    public static function image()
    {
        if(!isset($_GET['pathwayOptions']) || $_GET['pathwayOptions'] == '')
        {
            return -1;
        }

        $db = Database::getInstance();
        $req = $db->prepare('SELECT 1 FROM pathway WHERE Pathway_ID = :Pathway');
        $req->execute(array('Pathway' => $_GET['pathwayOptions']));
        $data = $req->fetch();

        if(!isset($data['1']) || $data['1'] != '1')
        {
            return -1;
        }

        $pathwayFile = $_GET['pathwayOptions'];

        if(isset($_SESSION['Pathway'][$pathwayFile]['Image'])) {
            echo  $_SESSION['Pathway'][$pathwayFile]['Image'];
        }

        return 0;
    }

    protected static function convertOrganismToDB($organism)
    {
        if($organism == 'Human')
        {
            return 'hsa';
        }
        else
        {
            return 'mmu';
        }
    }
}