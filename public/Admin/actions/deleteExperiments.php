<?php
require '../../../app/includes.php';

//if not logged in
if(!isset($_SESSION['User']['isAuth']) || !$_SESSION['User']['isAuth'] == 1) {
    header('Location: ../experiments.php');
}

$db = \iTLR\Database\Database::getInstance();

$db->query('TRUNCATE stimulation; TRUNCATE experiment_stimulation; TRUNCATE experiments; TRUNCATE platform; TRUNCATE experiment_stimulation; TRUNCATE experiment_gene');

header('Location: ../home.php');
