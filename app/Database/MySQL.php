<?php

namespace iTLR\Database;

class MySQL extends Database {

    /**
     * MySQL constructor.
     * @param string $environment
     * @param array $attributes
     */
    public function __construct(array $attributes) {
        $this->db = new \PDO('mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_DB'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));

        foreach($attributes as $key => $value) {
            $this->db->setAttribute($key, $value);
        }
    }

    /**
     * @return \PDO;
     */
    public function DBConnection() {
        return $this->db;
    }
}