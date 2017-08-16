<?php

namespace iTLR\HeatMap;


use iTLR\Database\Database;
use iTLR\Experiment\Experiment;
use iTLR\Output\CSV;
use iTLR\Visualizations\Support\Operation;
use iTLR\Visualizations\Support\Ranges;

class HeatMap
{

    private $_genes;
    private $_experiments;
    private $_csv;
    private $_heatMapParameters;

    /**
     * Constructs the class.
     */
    public function __construct($geneText, $geneFile)
    {
        $this->_genes = $this->filterInput($geneText, $geneFile);
        $this->_heatMapParameters = new HeatMapParameters($this->_genes);
        $this->initialize();
    }

    /**
     * Returns the final data in order to create the HeatMap
     */
    public function getCSV()
    {
        $csv = new CSV($this->_csv, false);
        $csv->output();
    }


    /**
     *  Returns the preliminary data in order to setup the HeatMap JS Code
     */
    public function getSetupJS()
    {
        $result = array('exp' => array_column($this->_experiments, 'Name'), 'genes' => $this->_genes);
        return json_encode($result);
    }

    /**
     * Fetches the data provided from the genes variable
     * Constructs an array needed to create the csv format
     * Constructs experiments variable to contain the experiments
     */
    private function initialize()
    {
        $this->_experiments = array();
        $this->_csv = array();


        $_SESSION['startTime'] = microtime(true);
        $this->_experiments = $this->_heatMapParameters->retrieveExpData();

        //for ($i = 0; $i < count($exp); $i++) {
        //    $this->_experiments[$i]['Name'] = $exp[$i]['Name'];
        //    $this->_experiments[$i]['ID'] = $exp[$i]['ID'];
        //    $expIds[] = $exp[$i]['ID'];
        //}

        //At this point we have the experiments; however, we do not know if each experiment has all the genes necessary

        /*if(count($expIds) >= 1) {
            $dataCompress = ((count($expIds) == 1) ? 'single' : 'intersection');
            $data = Data::getData('array', $dataCompress, null, $expIds);
            //print_modified_r($data);
            sort($this->_genes); //needed since the array given back is sorted alphabetically
            $result = array(array('Gene', 'Column', 'Row', 'ExperimentName', 'Value'));

        }*/

        //print_modified_r($this->_experiments);
        //print_modified_r($expIds);

        //echo count($expIds);
        if (count($this->_experiments) >= 1) {

            sort($this->_genes); //needed since the array given back is sorted alphabetically

            $result = array(array('Gene', 'Column', 'Row', 'ExperimentName', 'Value'));

            for ($i = 0; $i < count($this->_experiments); $i++) {
                for ($a = 0; $a < count($this->_experiments[$i][0]); $a++) {
                    $result[] = array($this->_genes[$a], $a + 1, $i + 1, $this->_experiments[$i]['Name'], $this->_experiments[$i][0][$this->_genes[$a]]);
                }
            }

            //print_modified_r($result);
            $this->_csv = $result;

            /*for ($i = 1; $i < count($data); $i++) {
                if (in_array($data[$i][0], $this->_genes)) {
                    for ($a = 1; $a < count($data[$i]); $a++) {
                        $result[] = array($data[$i][0], (array_search($data[$i][0], $this->_genes)+1), $a, $this->_experiments[$a - 1]['Name'], $data[$i][$a]);
                    }
                }
            }
            //print_modified_r($result);
            $this->_csv = $result;*/
        }
    }


    /**
     * @return array of the genes based on the text field or the text file
     */
    private function filterInput($geneText, $geneFile)
    {
        $geneText = str_replace(' ', '', $geneText);
        $genes = array();

        if (isset($geneText) && trim($geneText) != '') {
            $genes = array_values(array_filter(explode(',', $geneText)));
        } else if (isset($geneFile['name']) && $geneFile['name'] != '') {
            $handle = fopen($geneFile['tmp_name'], "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    //make sure there is a value
                    $geneTmp = str_replace(' ', '', trim($line));
                    $geneTmp = str_replace(',', '', $geneTmp);
                    if (!empty($geneTmp)) {
                        $genes[] = $geneTmp;
                    }
                }

                fclose($handle);
            }
        }
        return array_values(array_unique($genes));

    }

}


class HeatMapParameters
{
    private $_parameters = array('cellType', 'stimulation', 'dataType');
    private $_parameterValues;
    private $_groupBy;
    private $_genes;
    private $_debug = array(0);

    public function __construct($genes)
    {
        $this->_genes = $genes;
        $this->addExperimentFilter();
        $this->_groupBy = $_POST['groupBy'];
    }

    /**
     * Resolves the parameters specified in the page:
     * CellType, Stimulation and DataType
     */
    private function addExperimentFilter()
    {
        for ($i = 0; $i < count($this->_parameters); $i++) {
            $this->addSelectionParameters($this->_parameters[$i]);
        }
    }

    /**
     * Add selected type to the Parameters if available
     * @param type
     */
    private function addSelectionParameters($type)
    {
        if (!isset($_POST[$type])) return;

        for ($i = 0; $i < count($_POST[$type]); $i++) {
            $typeValue = $_POST[$type][$i];

            if ($typeValue == 'All' || $typeValue == '') {
                return;
            }

            $this->_parameterValues[] = array('Type' => $type, 'Value' => $typeValue);
        }
    }


    public function retrieveExpData()
    {
        //$exp = Data::findExperimentsIDFromGenes($this->_genes);
        $db = Database::getInstance();

        $heatmapQuery = new HeatMapQuery($this->_genes, $this->_parameterValues, $this->_groupBy);

        //echo $heatmapQuery->getWhereQuery();
        //print_modified_r($heatmapQuery->getWhereValues());

        $req = $db->prepare('SELECT DISTINCT experiments.Name, experiments.ID FROM experiments LEFT JOIN experiment_gene ON experiment_gene.ExperimentID = experiments.ID LEFT JOIN platform ON platform.ID = experiment_gene.GeneID LEFT JOIN `experiment_stimulation` AS es ON experiments.id = es.ExperimentID LEFT JOIN stimulation ON es.StimulationID = stimulation.ID WHERE platform.Gene IN ' . $heatmapQuery->getWhereQuery());
        $req->execute($heatmapQuery->getWhereValues());

        $exp = $req->fetchAll(\PDO::FETCH_ASSOC);

        //print_r($exp);

        if (count($exp) == 0) {
            return array();
        }

        //$dataCompress = ((count($exp) == 1) ? 'single' : 'union');
        //$data = Data::getData('array', $dataCompress, null, array_values(array_column($exp, 'ID')), 'Mean', $this->_genes);

        for($i = 0; $i < count($exp); $i++)
        {
            $exp[$i] = new Experiment($exp[$i]['ID']);
        }

        /** @var Experiment[] $exp */

        $ranges     = new Ranges(null);
        $operation  = new Operation('mean');

        $data = array(array());
        for($i = 0; $i < count($exp); $i++)
        {
            $data[$i]['Name']   = $exp[$i]->get('Name');
            $data[$i]['ID']     = $exp[$i]->get('ID');
            $genes = $exp[$i]->getData(array(Experiment::$gene, Experiment::$value), $ranges, $operation, $this->_genes)->fetchAll(\PDO::FETCH_ASSOC);

            for($a = 0; $a < count($genes); $a++)
            {
                $data[$i][0][$genes[$a]['Gene']] = $genes[$a]['Value'];
            }
        }

        //print_r($data);

        if (in_array(1, $this->_debug)) {
            print_modified_r($data);
        }

        //Filter out experiments that do not have the genes
        $experimentsWithData = $this->validateGenesInExp($data);

        //print_r($experimentsWithData);

        return $experimentsWithData;
    }

    /***
     * Transforms the union data to more grouped data and filters out any experiments that has an "N/A" value
     * @param $data
     * @param $exp
     */
    private function validateGenesInExp($data)
    {
        $validExperiments = array();

        $geneNo = count($this->_genes);

        for($i = 0; $i < count($data); $i++)
        {
            if(count($data[$i][0]) == $geneNo)
            {
                $validExperiments[] = $data[$i];
            }

        }

        if (in_array(1, $this->_debug)) {
            print_modified_r($validExperiments);
        }

        return $validExperiments;
    }


}


class HeatMapQuery
{
    private $_parametersDataBase = array('cellType' => 'experiments.CellType', 'stimulation' => 'stimulation.Stimulus',
        'dataType' => 'experiments.DataType');
    private $_groupBy;
    private $_whereQuery;
    private $_whereValues;
    private $_genes;
    private $_parameterValues;

    public function __construct($genes, $parameters, $groupBy)
    {
        $this->_genes = $genes;
        $this->_parameterValues = $parameters;
        $this->_groupBy = $groupBy;
        $this->createQuery();
    }

    public function getWhereQuery()
    {
        return $this->_whereQuery;
    }

    public function getWhereValues()
    {
        return $this->_whereValues;
    }


    private function createQuery()
    {
        $genesQuery = array();
        $genesValues = array();

        for ($i = 0; $i < count($this->_genes); $i++) {
            $genesQuery[] = '?';
            $genesValues[] = $this->_genes[$i];
        }

        $genesQuery = '(' . implode(',', $genesQuery) . ')';

        $whereQuery = array();
        $whereValues = array();

        for ($i = 0; $i < count($this->_parameterValues); $i++) {

            $type = $this->_parameterValues[$i]['Type'];
            $value = $this->_parameterValues[$i]['Value'];

            $type = $this->_parametersDataBase[$type];

            if (!isset($type)) {
                $whereQuery[$type] = array($type . ' = ?');
                $whereValues[] = $value;
            } else {
                $whereQuery[$type][] = $type . ' = ?';
                $whereValues[] = $value;
            }
        }

        $whereQueryKeys = array_keys($whereQuery);

        $whereQueryString = '';

        if (count($whereQueryKeys) > 0) {
            $whereQueryString = ' AND ';

            for ($i = 0; $i < count($whereQueryKeys); $i++) {
                if ($i == 0) {
                    $whereQueryString .= '(' . implode(' OR ', $whereQuery[$whereQueryKeys[$i]]) . ')';
                } else {
                    $whereQueryString .= ' AND (' . implode(' OR ', $whereQuery[$whereQueryKeys[$i]]) . ')';
                }
            }
        }

        if ($this->_groupBy != '') {
            $this->_groupBy = $this->validateGroupBy($this->_groupBy);
            $whereQueryString .= ' ORDER BY ' . $this->_groupBy . ' ASC';
        }

         //var_dump($genesQuery);
         //var_dump($whereQueryString);

         //print_modified_r($genesValues);
         //print_modified_r($whereValues);
         //print_modified_r($this->_groupBy);

        $this->_whereQuery = $genesQuery . $whereQueryString;
        $this->_whereValues = array_merge($genesValues, $whereValues);

        //var_dump($this->_whereQuery);
        //var_dump($this->_whereValues);
        //print_modified_r($this->_whereValues);
    }

    private function validateGroupBy($groupBy)
    {
        if ($groupBy == 'Cell Type') {
            $groupBy = 'experiments.CellType';
        } else if ($groupBy == 'Stimulation') {
            $groupBy = 'stimulation.Stimulus';
        } else if ($groupBy == 'Data Type') {
            $groupBy = 'experiments.DataType';
        } else if ($groupBy == 'Time Point') {
            $groupBy = 'experiments.TimePoint';
        } else if ($groupBy == 'Experimentalist') {
            $groupBy = 'experiments.Experimentalist';
        } else if ($groupBy == 'Replicate') {
            $groupBy = 'experiments.Replicate';
        } else if ($groupBy == 'Strain') {
            $groupBy = 'experiments.Strain';
        } else {
            $groupBy = 'experiments.CellType';
        }

        return $groupBy;
    }


}