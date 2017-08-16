<?php

require_once '../../../app/includes.php';

/** @var \iTLR\Parameters\ExperimentParametersManagement $parameters */
$parameters = $_SESSION['Parameters'];

$valid = $parameters->allExperimentsCompleted();

//Validate the Request
if(!isset($_GET['filter']))
{
	$valid = false;
}
if(!isset($_GET['operation'])) {
	$valid = false;
}

if(!isset($_GET['type'])) {
	$valid = false;
}

if($valid == true || (isset($_GET['type']) && $_GET['type'] == 'pathwayImage')) {
	switch($_GET['type']) {
		case 'retrieveAllIntersect':
			session_write_close();
			\iTLR\Visualizations\ScatterPlot::output();
			break;
		case 'getCorrelation':
			session_write_close();
			\iTLR\Visualizations\Correlation::output();
			break;
		case 'chord':
			session_write_close();
			\iTLR\Visualizations\Chord::output();
			break;
		case 'dataTable':
			session_write_close();
			\iTLR\Visualizations\DataTable::output();
			break;
		case 'network':
			session_write_close();
			\iTLR\Visualizations\Network::output();
			break;
		case 'pathway':
			\iTLR\Visualizations\Pathway::output();
			break;
		case 'pathwayImage':
			\iTLR\Visualizations\Pathway::image();
			break;
	}
}



/*if($valid == true || $_GET['type'] == 'pathwayImage') {
	switch($_GET['type']) {
		case 'retrieveAllIntersect':
			Data::getData('csv', 'intersection', null, null, $_GET['operation']);
			break;
		case 'getCorrelation':
			Data::getCorrelation('intersection', $_GET['operation']);
			break;
		case 'chord':
			Data::chordDiagramMatrix($_GET['id'], $_GET['ranges'], $_GET['operation']);
			break;
		case 'dataTable':
			Data::getData('json', $_GET['filter'], $_GET['ranges'], null, $_GET['operation']);
			break;
		case 'network':
			Data::makeNetwork($_GET['filter'], $_GET['ranges'], $_GET['operation']);
			break;
		case 'pathway':
			Pathway::generate($_GET['pathwayOptions'], $_GET['ranges'], $_GET['filter'], $_GET['operation']);
			break;
		case 'pathwayImage':
			Pathway::printImage($_GET['pathwayOptions']);
			break;
	}
}*/


?>
