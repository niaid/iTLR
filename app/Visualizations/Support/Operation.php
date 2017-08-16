<?php namespace iTLR\Visualizations\Support;

/**
 * The operation to execute on the database for grouping the same genes
 *
 * Default value is provided in case of alteration by the user
 *
 * Class Operation
 * @package iTLR\VisualizationSupport
 */
class Operation {

    protected $_operation;

    public function __construct($operation)
    {
        $this->_operation = $this->convert($operation);
    }

    protected function convert($operation)
    {
        $operation = strtolower($operation);

        switch($operation) {
            case 'mean':
                return 'AVG';
            case 'min':
                return 'MIN';
            case 'max':
                return 'MAX';
            default:
                return 'AVG';
        }
    }

    /**
     * Returns the current filter
     * @return string
     */
    public function get()
    {
        return $this->_operation;
    }

}

