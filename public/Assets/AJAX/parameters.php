<?php
require_once '../../../app/includes.php';

use iTLR\Parameters\ExperimentParametersManagement;

/* AJAX request will return JSON */
header('Content-Type: application/json');
$json = array('Status' => 0);

if (!isset($_GET['params']) || !isset($_GET['id'])) {
    /* If the "params" payload is not set */
    $json['Status'] = 1;
    return json_encode($json);
}

if ($_GET['params'] == 'All') {
    /* Must be a new parameters request */
    if (!isset($_SESSION['Parameters'])) {
        $_SESSION['Parameters'] = new ExperimentParametersManagement();
    }

    $_SESSION['Parameters']->newParameter($_GET['id']);

}

if ($_GET['params'] == 'Partial') {
    if ($_GET['state'] == 'unpressed') {
        $_SESSION['Parameters']->addSelection($_GET['id'], $_GET['type'], $_GET['value']);
    } else {
        $_SESSION['Parameters']->removeSelection($_GET['id'], $_GET['type'], $_GET['value']);
    }
}

if($_GET['params'] == 'Reset') {
    $_SESSION['Parameters']->reset($_GET['id']);
}

if($_GET['params'] == 'Delete') {
    $_SESSION['Parameters']->delete($_GET['id']);
}

$json = array_merge($json, ($_SESSION['Parameters']->getJSON($_GET['id'])));

session_write_close();

echo json_encode($json);