<?php namespace iTLR\Log;

use Monolog\Logger;

class Log {

    public static $instance;

    /**
     * @var Logger
     */
    protected $logger;
    protected $handler;

    public function __construct()
    {
        $this->logger = new Logger('MySQL');
        $this->logger->pushHandler($this->handler);
    }

    /**
     * @return Logger
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new MySQL();
        }

        return self::$instance->getLogger();
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->logger;
    }




}