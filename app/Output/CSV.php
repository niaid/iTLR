<?php namespace iTLR\Output;

class CSV extends ArrayClass
{

    protected $_headers;

    public function __construct(array &$data, bool $headers = true)
    {
        $this->_data = $data;

        if($headers == true)
        {
            $this->_headers = array_keys($data[0]);
        }
        else
        {
            $this->_headers = null;
        }
    }

    public function output()
    {
        $outputBuffer = fopen("php://output", 'w');

        if($this->_headers != null)
        {
            fputcsv($outputBuffer, $this->_headers);
        }

        foreach($this->_data as $row) {
            fputcsv($outputBuffer, $row);
        }
        fclose($outputBuffer);
    }


}