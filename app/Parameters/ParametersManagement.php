<?php

namespace iTLR\Parameters;

use iTLR\Experiment\Experiment;

abstract class ParametersManagement
{
    /**
     * @var Parameters[];
     */
    protected $_parameters;

    /**
     * This contains the keys which are based on the Tab values
     * @var array
     */
    protected $_parameterKeys;

    /**
     * ParametersManagement constructor.
     */
    public function __construct()
    {
        $this->_parameters;
        $this->_parameterKeys = array();
    }

    public function newParameter($id)
    {
        $this->_parameters[$id] = new Parameters();
        $this->_parameterKeys[] = $id;
        ksort($this->_parameterKeys);
    }

    public function addSelection($id, $type, $value)
    {
        $this->_parameters[$id]->addSelection($type, $value);
        $this->_parameterKeys = array_values($this->_parameterKeys);
    }

    public function removeSelection($id, $type, $value)
    {
        $this->_parameters[$id]->removeSelection($type, $value);
        $this->_parameterKeys = array_values($this->_parameterKeys);
    }

    public function reset($id)
    {
        $this->_parameters[$id]->reset();
    }

    public function delete($id)
    {
        unset($this->_parameters[$id]);
        unset($this->_parameterKeys[array_search($id, $this->_parameterKeys)]);
        $this->_parameterKeys = array_values($this->_parameterKeys);
    }

    public function getKeys()
    {
        return $this->_parameterKeys;
    }

    public function exists($id)
    {
        return isset($this->_parameters[$id]);
    }

    /**
     * @param $id
     * @return Experiment|false|null
     */
    public function isCompleted($id)
    {
        return $this->_parameters[$id]->isCompleted();
    }

    /**
     * @return bool
     */
    public function allExperimentsCompleted() {
        $allExperimentsCompleted = true;
        $parametersCount = count($_SESSION['Parameters']);

        for($i = 0; $i < $parametersCount; $i++) {
            $currentTab = $this->_parameterKeys[$i];

            $experiment = $this->_parameters[$currentTab]->getExperiment();
            if($experiment === false && $experiment === null) {
                $allExperimentsCompleted = false;
            }
        }

        return $allExperimentsCompleted;
    }

    abstract public function getJSON($id);
}
