<?php
header('X-Powered-By: U.S. Government:NIH/NIAID');

chdir(__DIR__);

require_once('../vendor/autoload.php');

use iTLR\Database\MySQL as MySQL;


/* Start the PHP session and name it to be NIH */
session_name('NIH');

/* Start the application session */
session_start();

/* Load the environment variables from .env */
$dotEnv = new Dotenv\Dotenv(dirname(__DIR__));
$dotEnv->load();

/* Set up the database */
$dbAttributes = array();

if(getenv('APP_ENV') == 'development')
{
    /* Set the attributes for the database connection */
    $dbAttributes = array(  PDO::ATTR_ERRMODE             => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_EMULATE_PREPARES    => true);
}

MySQL::getInstance($dbAttributes);



