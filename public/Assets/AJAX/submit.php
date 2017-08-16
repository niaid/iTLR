<?php
require_once '../../../app/includes.php';
use iTLR\Output\JSONRequest;
use iTLR\Helpers\Prints;

$json = new JSONRequest(JSONRequest::$valid);

/** @var \iTLR\Parameters\ExperimentParametersManagement $parameters */
$parameters = $_SESSION['Parameters'];

if(!isset($_POST['GetInfo']) || $_POST['GetInfo'] != 1)
{
    $json->changeStatus(JSONRequest::$error);
    $json->changeMessage('An error has occurred');
    $json->output();

    //logging
    $log = \iTLR\Log\Log::getInstance();
    $log->addError('Submit.php: GetInfo was not in POST Request'.Prints::implodeAssociateArray($_POST));

    exit();
}

//Addition of a few important properties
$json->add('ReadyToSubmit', JSONRequest::$valid);
$json->add('OneOrganism', JSONRequest::$valid);

$parameterKeys = $parameters->getKeys();
$parameterKeysCount = count($parameterKeys);
$experiments = array();
$experimentData = array();
$organism = null;

/* Go through each of the parameter keys */
for($i = 0; $i < $parameterKeysCount; $i++)
{
    $currentTab = $parameterKeys[$i];

    if(!is_numeric($currentTab) && !$parameters->exists($currentTab)) {
        /* Determine if the key is part of the parameters */
        $experiments[$currentTab]['Status'] = 'An error has occurred';
    }


    $experiment = $parameters->isCompleted($currentTab);


    if($experiment == false)
    {
        /* If the data was not completed */
        $experiments[$currentTab]['Status'] = 'Not Completed';
        $json->change('ReadyToSubmit', JSONRequest::$error);
    }
    else if($experiment == null)
    {
        /* If the experiment was not found */
        $experiments[$currentTab]['Status'] = 'No experiment found';
        $json->change('ReadyToSubmit', JSONRequest::$error);
    }
    else
    {
        $experiment->setTabNo($currentTab);
        $experiment->setOrder($i);
        $experimentData[] = $experiment;
        $experiments[$currentTab] = $experiment->getInfo();
        $experiments[$currentTab]['geneNo'] = $experiment->geneNo();
        $experiments[$currentTab]['Status'] = 'Completed';

        /* Does the organism match with the other experiments */
        if($organism == null)
        {
            /* If the organism variable was not set */
            $organism = $experiment->get("organism");
        }
        else if($organism != $experiment->get("organism"))
        {
            /* If the organism variable differ */
            $json->change('OneOrganism', JSONRequest::$error);
            $json->change('ReadyToSubmit', JSONRequest::$error);
        }
    }
}

if($json->get('ReadyToSubmit') == JSONRequest::$valid) {
    $_SESSION['Experiments'] = new \iTLR\Experiment\Experiments($experimentData);
}
else if(isset($_SESSION['Experiments'])) {
    unset($_SESSION['Experiments']);
}

session_write_close();

$json->add('Data', $experiments);
echo $json->output();
?>