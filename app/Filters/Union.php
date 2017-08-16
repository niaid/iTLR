<?php namespace iTLR\Filters;

use iTLR\Experiment\Experiment;
use iTLR\Helpers\Prints;

class Union extends Method {

    protected $unionGenes;


    protected function handle()
    {
        $this->retrieveGenes();
        $this->union();
        $this->unionJoin();
    }

    protected function retrieveGenes()
    {
        $columns = array(Experiment::$gene, Experiment::$value);

        foreach ($this->_experiments->get() as $experiment)
        {
            $ranges = $this->_ranges->getRangesForExperiment($experiment);
            $this->_data[] = $experiment->getData($columns, $ranges, $this->_operation)->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    protected function union()
    {
        $genes = array_column($this->_data[0], 'Gene');
        $experimentNo = $this->_experiments->count();

        for($i = 1; $i < $experimentNo; $i++)
        {
            $genes = array_merge($genes, array_column($this->_data[$i], 'Gene'));
        }

        sort($genes);
        $genes = array_values(array_unique($genes));

        $this->unionGenes = $genes;
    }

    protected function unionJoin()
    {
        //TODO: Review
        $unionGenesNo = count($this->unionGenes);
        $experiments = $this->_experiments->get();
        $experimentNo = count($experiments);

        $dataArrayIterator = array();
        for($i = 0; $i < $experimentNo; $i++) // Set the iterator for each experiment to 0
        {
            $dataArrayIterator[] = 0;
        }

        for($i = 0; $i < $unionGenesNo; $i++) //Running through the entire list of union genes
        {
            $this->unionGenes[$i] = array('Gene' => $this->unionGenes[$i]);

            for($a = 0; $a < $experimentNo; $a++)
            {
                if(isset($this->_data[$a][$dataArrayIterator[$a]]) &&
                    $this->unionGenes[$i]['Gene'] == $this->_data[$a][$dataArrayIterator[$a]]['Gene'])
                {
                    $this->unionGenes[$i]['Data'.$experiments[$a]->getTabNo()] = $this->_data[$a][$dataArrayIterator[$a]]['Value'];
                    $dataArrayIterator[$a]++;
                }
                else
                {
                    $this->unionGenes[$i]['Data'.$experiments[$a]->getTabNo()] = 'N/A';
                }
            }

            $dataArrayIterator++;
        }

        $this->_data = $this->unionGenes;
    }

    public function getData()
    {
        return $this->_data;
    }
}