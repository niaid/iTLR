<?php namespace iTLR\Log;

use iTLR\Database\Database;
use MySQLHandler\MySQLHandler;

class MySQL extends Log{


    public function __construct()
    {
        $this->handler = new MySQLHandler(Database::getInstance(), "log");
        parent::__construct();
    }
}