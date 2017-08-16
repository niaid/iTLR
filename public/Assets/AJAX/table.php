<?php

require_once '../../../app/includes.php';

$debug = array('download' => true);

if($debug['download']) {
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=experiment_data.csv");
    header("Content-Transfer-Encoding: binary");
}

\iTLR\Visualizations\Download::output();
