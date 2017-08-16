<?php
require_once '../../../app/includes.php';

use \iTLR\Upload\Experiments;
use iTLR\Helpers\Prints;

//if not logged in
if(!isset($_SESSION['User']['isAuth']) || !$_SESSION['User']['isAuth'] == 1) {
    header('Location: ../experiments.php');
}
    //phpinfo();
    echo '<pre>';
    print_r($_FILES);
    echo '</pre>';
    echo '<br/><br/><br/>';

    $timeStart = microtime(true);

    //$result = \iTLR\Upload\Experiments::insertIntoDB('Data', 'Info');
    $uploadExperiment = new Experiments('Data', 'Info');
    echo 'Total: '.(microtime(true) - $timeStart);
	//print_r($result);

?><!DOCTYPE html>
<html>
    <head>
        <title>Uploaded</title>
    </head>
    <body>
        <h1>Information:</h1>
        <p style="color:red;">
        <?php
            if(!$uploadExperiment->isValid()) {
                echo 'Errors<br/>';
                Prints::array($uploadExperiment->getErrors());
            } if(count($uploadExperiment->getWarnings()) > 0) {
                echo 'Warnings<br/>';
                Prints::array($uploadExperiment->getWarnings());
            } if(count($uploadExperiment->getSkippedRows()) > 0) {
                echo 'SkippedRows<br/>';
                Prints::array($uploadExperiment->getSkippedRows());
            }
        ?>
        </p>


    </body>
</html>
