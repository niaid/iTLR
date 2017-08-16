<?php namespace iTLR\Experiment;

class Experiments {

    /**
     * @var Experiment[]
     */
    protected $_experiments;

    /**
     * @var int
     */
    protected $_experimentNo;

    /**
     * Experiments constructor.
     * @param $experiments
     */
    public function __construct($experiments)
    {
        $this->_experiments = $experiments;
        $this->_experimentNo = count($this->_experiments);
    }

    /**
     * Return experiments in order of their tabNo
     * @return Experiment[]
     */
    public function get()
    {
        $experiments = array();

        $tabNos = array();
        foreach ($this->_experiments as $experiment)
        {
            $tabNos[] = $experiment->getTabNo();
        }

        sort($tabNos);

        for($i = 0; $i < $this->_experimentNo; $i++)
        {
            foreach ($this->_experiments as $experiment)
            {
                if($experiment->getTabNo() == $tabNos[$i])
                {
                    $experiments[] = $experiment;
                }
            }
        }

        return $experiments;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->_experimentNo;
    }

    
}