<?php

namespace iTLR\Session;

class Session {

    public static function cleanSlate() {
        $keys = array_keys($_SESSION);
        foreach($keys as $key) {
            unset($_SESSION[$key]);
        }
    }
}

?>