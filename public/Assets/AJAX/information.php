<?php

require_once '../../../app/includes.php';

session_write_close();

if(isset($_GET['type']) == 'pathwayFetch') {
    $experiments = $_SESSION['Experiments'];

    echo json_encode(\iTLR\Visualizations\Pathway::retrieve($experiments));
}


?>