<?php

require_once('../../../app/includes.php');

use iTLR\HeatMap\HeatMap;

if(isset($_POST['geneText']) && isset($_FILES['geneFile'])) {
    $_SESSION['heatmap'] = new HeatMap($_POST['geneText'], $_FILES['geneFile']);
    echo $_SESSION['heatmap']->getSetupJS();
} else if(isset($_GET['data']) && $_GET['data'] == 'csv' && isset($_SESSION['heatmap'])) {
    echo $_SESSION['heatmap']->getCSV();
}
?>