<?php namespace iTLR\Filters;

use iTLR\Experiment\Experiment;


class Intersection extends Method
{

    protected $_intersectGenes;

    protected function handle()
    {
        $this->retrieveGenes();
        $this->runIntersectionOnGenes();
        $this->retrieveValuesForEachGene();
    }

    protected function retrieveGenes()
    {
        $columns = array(Experiment::$gene);

        foreach ($this->_experiments->get() as $experiment)
        {
            $ranges = $this->_ranges->getRangesForExperiment($experiment);
            $this->_data[] = $experiment->getData($columns, $ranges, $this->_operation)->fetchAll(\PDO::FETCH_COLUMN);
        }
    }

    protected function runIntersectionOnGenes()
    {
        $this->_intersectGenes = array_values(call_user_func_array('array_intersect', $this->_data));
    }

    protected function retrieveValuesForEachGene()
    {
        $this->_data = $this->_intersectGenes;
        $geneNo = count($this->_data);


        $columns = array(Experiment::$gene, Experiment::$value);


        $experiments = $this->_experiments->get();
        $experimentNo = count($experiments);

        for($a = 0; $a < $experimentNo; $a++)
        {
            $ranges = $this->_ranges->getRangesForExperiment($experiments[$a]);
            $values = $experiments[$a]->getData($columns, $ranges, $this->_operation)
                                    ->fetchAll(\PDO::FETCH_ASSOC);

            $valuesNo = count($values);

            $dataI = 0;

            $tabNo = $experiments[$a]->getTabNo();

            for($i = 0; $i < $valuesNo; $i++)
            {
                if($a == 0)
                {
                    if ($values[$i]['Gene'] == $this->_data[$dataI])
                    {
                        $this->_data[$dataI] = array('Gene' => $this->_data[$dataI], 'Data' . $tabNo => $values[$i]['Value']);
                        $dataI++;
                    }
                }
                else
                {
                    if ($values[$i]['Gene'] == $this->_data[$dataI]['Gene'])
                    {
                        $this->_data[$dataI]['Data' . $tabNo] = $values[$i]['Value'];
                        $dataI++;
                    }
                }

                if($dataI == $geneNo)
                {
                    break;
                }
            }
        }
    }

    public function getData()
    {
        return $this->_data;
    }


}