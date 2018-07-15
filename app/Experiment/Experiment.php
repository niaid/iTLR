<?php

namespace iTLR\Experiment;

use iTLR\Database\Database;
use iTLR\Visualizations\Support\Operation;
use iTLR\Visualizations\Support\Ranges;

class Experiment
{
    protected $_id;
    protected $_info;
    protected $_tab;
    protected $_order;

    public static $id = 'ID';

    /* Columns */
    public static $gene     = 'Gene';
    public static $platform = 'Platform';
    public static $value    = 'Value';

    public function __construct($id = -1, array $array = null)
    {
        if($id >= 0) {
            $this->populateById($id);
        }
    }

    public function setOrder($order)
    {
        $this->_order = $order;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function setTabNo(int $tab)
    {
        $this->_tab = $tab;
    }

    public function getTabNo()
    {
        return $this->_tab;
    }

    public function getID()
    {
        return $this->_id;
    }

    private function populateById($exp_id)
    {
        $db = Database::getInstance();

        $req = $db->prepare('SELECT DISTINCT experiments.ID, experiments.Name, experiments.DataType AS dataType, experiments.CellType AS cellType, stimulation.Stimulus AS stimulation, experiments.Strain AS strain, experiments.TimePoint AS timePoint, stimulation.Concentration AS concentration, experiments.Experimentalist AS experimentalist, experiments.Replicate AS replicate, Organism AS organism, Protocol AS protocol FROM experiments LEFT JOIN `experiment_stimulation` AS es ON experiments.id = es.ExperimentID LEFT JOIN stimulation ON es.StimulationID = stimulation.ID WHERE experiments.ID = :ID');
        $req->execute(array('ID' => $exp_id));

        if($req->rowCount() == 0) {
            return null;
        }

        $this->_info = $req->fetch(\PDO::FETCH_ASSOC);
        $this->_id = $exp_id;

        /* Retrieve the all the stimulations and concentrations */
        while($data = $req->fetch(\PDO::FETCH_ASSOC)) {
            if (!is_array($this->_info['stimulation'])) {
                $this->_info['stimulation'] = array($this->_info['stimulation'], $data['stimulation']);
                $this->_info['concentration'] = array($this->_info['concentration'], $data['concentration']);
            } else {
                $this->_info['stimulation'][] = $data['stimulation'];
                $this->_info['concentration'][] = $data['concentration'];
            }
        }
    }

    public function geneNo() {
        $db = Database::getInstance();

        $req = $db->prepare('SELECT DISTINCT platform.Gene FROM platform LEFT JOIN experiment_gene ON experiment_gene.GeneID = platform.id LEFT JOIN experiments ON experiments.id = experiment_gene.ExperimentID WHERE experiments.id = :id');
        $req->execute(array('id' => $this->_id));

        return $req->rowCount();
    }

    public function get($type)
    {
        if (!isset($this->_info[$type])) {
            return null;
        }

        return $this->_info[$type];
    }

    public function getJSON() {
        return json_encode($this->_info);
    }

    public function getInfo() {
        return $this->_info;
    }


    public function getData(array $columns, Ranges $ranges, Operation $operation, &$selectGenes = null)
    {
        $db = Database::getInstance();
        $columnsFetch = array();
        $whereValues = array($this->_id);
        $whereRanges = '';
        $whereSelectGenes = '';

        if(in_array('Gene', $columns)) {
            $columnsFetch[] = 'platform.Gene';
        } if(in_array('Platform', $columns)) {
        $columnsFetch[] = 'platform.Alias AS Platform';
        } if(in_array('Value', $columns)) {
            $columnsFetch[] = $operation->get().'(experiment_gene.Value) AS Value';
        }

        if($selectGenes != null && count($selectGenes) > 0) {
            $whereSelectGenes = 'AND platform.Gene IN (';
            $questionMarks = array();
            for($i = 0; $i < count($selectGenes); $i++) {
                $questionMarks[] = '?';
                $whereValues[] = $selectGenes[$i];
            }
            $whereSelectGenes .= implode(',', $questionMarks) . ')';
        }

        if(!$ranges->isNull()) {
            $whereRanges = ' HAVING '.$operation->get().'(experiment_gene.Value) > ? AND '.$operation->get().'(experiment_gene.Value) < ?';
            $range = $ranges->getRange();
            $whereValues[] = $range[0];
            $whereValues[] = $range[1];
            //print_r($whereValues);
        }


        //var_dump($whereSelectGenes);
        //print_modified_r($whereValues);
        //echo $whereRanges;
        //echo $whereQuery;
        //print_r($whereValues);
        //echo implode(' , ', $columnsFetch);

        $req = $db->prepare('SELECT '.implode(' , ', $columnsFetch).' FROM experiments LEFT JOIN `experiment_gene` ON experiments.ID = experiment_gene.ExperimentID LEFT JOIN `platform` ON experiment_gene.GeneID = platform.id WHERE experiments.ID = ? '.$whereSelectGenes.' GROUP BY Gene '.$whereRanges.' ORDER BY Gene ASC');
        $req->execute($whereValues);

        return $req;
    }
}