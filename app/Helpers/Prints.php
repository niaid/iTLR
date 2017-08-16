<?php

namespace iTLR\Helpers;

class Prints {
    protected $_debug;


    public function __construct(int $debug)
    {
        $this->_debug = $debug;
    }

    public function print($debug, $message, $description = '') {
        if($debug > $this->_debug) {
            return;
        }

        if($description != '') {
            echo $description . PHP_EOL;
        }

        if(is_array($message)) {
            self::array($message);
        } else if(is_numeric($message)) {
            self::number($message);
        } else {
            self::string($message);
        }
    }

    public static function array($array) {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }

    public static function number(int $number) {
        echo $number.PHP_EOL;
    }

    public static function string($string) {
        echo $string.PHP_EOL;
    }

    public static function implodeAssociateArray($array) {
        $string = PHP_EOL;

        foreach ($array as $key => $value) {
            $string .= $key.':'.$value.PHP_EOL;
        }

        return $string;
    }
}

