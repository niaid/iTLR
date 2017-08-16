<?php
namespace iTLR\Database;

abstract class Database {
    protected $db; /* The database connection */

    public static  $REMOTE = 'remote';
    public static  $LOCAL  = 'local';

    public static $instance;

    abstract protected function __construct(array $attributes);

    /**
     * @return \PDO;
     */
    abstract public function DBConnection();

    /**
     * @param string $environment
     * @param array $attributes
     * @return \PDO;
     */
    public static function getInstance(array $attributes = array())
    {
        if (!self::$instance) {
            self::$instance = new static($attributes);
        }

        return self::$instance->DBConnection();
    }

    /* proxy calls to non existent methods on this class to PDO instance */
    public function __call($method, $args)
    {
        $callable = array($this->db, $method);

        if (is_callable($callable)) {
            return call_user_func_array($callable, $args);
        }

        throw new PDOCallableException('No callable function found');
    }

    
    public function __destruct()
    {
        $db = null;
    }
}

?>