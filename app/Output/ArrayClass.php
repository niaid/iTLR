<?php namespace iTLR\Output;

abstract class ArrayClass {

    /**
     * @var array
     */
    protected $_data;

    /**
     * Adds the value to the array
     * @param $key
     * @param $value
     */
    public function add($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * Changes the value
     * @param $key
     * @param $value
     */
    public function change($key, $value)
    {
        $this->add($key, $value);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        if(!isset($this->_data[$key])) {
            return null;
        }

        return $this->_data[$key];
    }

    /**
     * Removes the entry from the array
     *
     * @param $key
     */
    public function remove($key)
    {
        unset($this->_data[$key]);
    }

    abstract public function output();
}