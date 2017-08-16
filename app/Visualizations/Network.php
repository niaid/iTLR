<?php namespace iTLR\Visualizations;

use iTLR\Database\Database;

class Network extends Visualization
{
    protected $_message;
    protected static $maxGenes = 250;

    protected function validate()
    {
        return 0;
    }

    protected function handle()
    {
        $tabsNo = $this->_experiments->count();
        $db = Database::getInstance();
        $this->_data['Message'] = '';

        $data =	$this->_filter->handle($this->_experiments, $this->_operation, $this->_ranges)->getData();

        $genes = array();
        for($i = 0; $i < count($data); $i++) {
            $genes[] = $data[$i]['Gene'];
        }

        $genes = "'" . implode("','", $genes) . "'";

        $organism = $this->_experiments->get()[0]->get('organism');

        $req = $db->prepare('SELECT DISTINCT Gene_A, Gene_B FROM network WHERE Gene_A IN ('.$genes.') AND Gene_B IN ('.$genes.') AND Organism = :Organism');
        $req->execute(array('Organism' => $organism));
        $network = $req->fetchAll(\PDO::FETCH_ASSOC);

        $genes = array();
        $networkNo = count($network);
        for($i = 0; $i < $networkNo; $i++) {
            $genes[] = $network[$i]['Gene_A'];
            $genes[] = $network[$i]['Gene_B'];
        }

        //echo count(array_values(array_unique($genes)));
        $genes = array_values(array_unique($genes));
        $geneNo = count($genes);
        $dataGenesColumn = array_column($data, 0);

        //if more than user threshold
        if((isset($_GET['geneNo']) && $_GET['geneNo'] != '' && is_numeric($_GET['geneNo']) && count($genes) > $_GET['geneNo'])) {
            $this->_data['Message'] = 'Since they are '.$geneNo.' genes in this network, the network cannot be rendered. Please refine your brush under \'Scatter Plots\' or continue to increase the threshold.';
            return;
        }
        else if(($_GET['geneNo'] == '' || !is_numeric($_GET['geneNo'])) && count($genes) > self::$maxGenes)
        {
            //if more than default threshold
            $this->_data['Message'] = 'Since they are '.$geneNo.' genes in this network, the network cannot be rendered as the default threshold is '.self::$maxGenes.'. Please refine your brush under \'Scatter Plots\' or manually increase the threshold.';
            return;
        }

        if($networkNo == 0)
        {
            $this->_data['Message'] = 'There is no gene network for the selected genes';
            return;
        }

        for($i = 0; $i < $networkNo; $i++) {
            if(in_array($network[$i]['Gene_A'], $genes) && in_array($network[$i]['Gene_B'], $genes)) {
                $this->_data['edges'][$i]['data']['source'] = $network[$i]['Gene_A'];
                $this->_data['edges'][$i]['data']['target'] = $network[$i]['Gene_B'];
            }
        }
        $this->_data['edges'] = array_values($this->_data['edges']);

        $experiments = $this->_experiments->get();


        for($i = 0; $i < $geneNo; $i++) {
            $this->_data['dataDist'][$i]['data']['id'] = $genes[$i];

            $count = 0;
            $expInData = array();
            $geneIndex = array_search($genes[$i], $dataGenesColumn);

            for($a = 1; $a <= $tabsNo; $a++) {
                $currentTab = $experiments[$a-1]->getTabNo();
                if($data[$geneIndex]['Data'.$currentTab] != 'N/A' || $data[$geneIndex]['Data'.$currentTab] == 0) { // 0 since 0 != 'N/A' returns false
                    $expInData[] = $currentTab;
                    $count++;
                }
            }

            for($a = 0; $a < $tabsNo; $a++) {
                $currentTab= $experiments[$a]->getTabNo();
                if(in_array($currentTab, $expInData)) {
                    $this->_data['dataDist'][$i]['data']['data'.$currentTab] = 10/$count;
                } else {
                    $this->_data['dataDist'][$i]['data']['data'.$currentTab] = 0;
                }
            }
        }
    }

    protected function outputData()
    {
        echo json_encode($this->_data);
    }
}