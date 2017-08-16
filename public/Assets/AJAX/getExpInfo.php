<?php
/*******************
 * PURPOSE: To retrieve data about an experiment selected
 *
 * @var \iTLR\Parameters\ExperimentParametersManagement $parameters
 */

require_once '../../../app/includes.php';

use iTLR\Output\JSONRequest;

$parameters = $_SESSION['Parameters'];
session_write_close(); //we do not need to write to the session

header('Content-type: application/json'); //This request will return a JSON object.

//The JSON object
$json = new JSONRequest(JSONRequest::$valid);

//Checking the parameters
if(!isset($_GET['id']) && !isset($_GET['type']))
{
    //Insufficient parameters
    $json->changeStatus(JSONRequest::$error);
    $json->changeMessage('Input parameters invalid');

    echo $json->output();
    exit();
}

//Reassign variables for clarity
$tabsId = $_GET['id'];
$type   = $_GET['type'];

$experiment = $parameters->isCompleted($tabsId);

if($experiment !== false && $experiment !== null)
{
    $protocol = $experiment->get($type);

    if($protocol == '') {
        $json->changeMessage('No protocol on file');
    } else {
        $json->changeMessage($protocol);
    }
}
else
{
    $json->changeStatus(JSONRequest::$error);
    $json->changeMessage('No valid experiment found');
}

echo $json->output();

?>