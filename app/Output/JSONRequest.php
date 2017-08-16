<?php namespace iTLR\Output;

class JSONRequest extends ArrayClass{

    /**
     * @var string
     */
    protected static $status = 'Status';

    /**
     * @var string
     */
    protected static $message  = 'Message';

    public static $valid = 1;
    public static $error = 0;

    /**
     * JSONRequest constructor.
     * @param int $status - valid
     * @param string $message
     *
     * Sets default values
     */
    public function __construct(int $status = 1, $message = '')
    {
        $this->_data[self::$status] = $status;
        $this->_data[self::$message] = $message;
    }

    /**
     * @param int $status
     */
    public function changeStatus(int $status)
    {
        $this->_data[self::$status] = $status;
    }

    /**
     * @param string $message
     */
    public function changeMessage(string $message)
    {
        $this->_data[self::$message] = $message;
    }

    public function output()
    {
        echo json_encode($this->_data);
    }
}