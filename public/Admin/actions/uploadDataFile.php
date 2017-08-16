<?php
	require '../../../Private/includes.php';

	//if not logged in
	if(!isset($_SESSION['User']['isAuth']) || !$_SESSION['User']['isAuth'] == 1) {
		header('Location: ../experiments.php');
	}
	
?><!DOCTYPE html>
<html>
	<head>
		<title>Experiment Parameters</title>
		<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet" />
		<style type="text/css">
		.uploads{
                padding:15px;
                border-radius:15px 15px 15px 15px;
                box-shadow:-1px 1px 1px rgba(0,0,0,0.15);
                background:#fff;
                border: 5px solid #3498db;
                margin-left: auto;
                margin-right: auto;
                width:1125px;
            }
            body{
            	background-color: gainsboro;
                font-family: "Comic Sans MS";
            }
            .float{
            	  float: left;
  margin-right: 50px;
  width: 345px;
            }
            .button{
            	padding: 2px 5px 2px 5px;
                border-radius: 10px;
                background-color: white; 
                text-decoration: none;
                font-family: "Comic Sans MS";
                font-weight: bold;
                margin-left: -3px;
                cursor: pointer;
                margin-top: 10px;
                border: 3px solid #3498db; color: #3498db;
            }
		</style>

	</head>
	<body>
		<div class="uploads">
<?php

	$csv = Upload::handleUpload('file1');
	
	$eperiments = array();

	//preload queries
	$expTable = $db->prepare('INSERT INTO experiments(Name) VALUES(:name)');

	$count = 0;
	for($i = 2; $i < count($csv[0]); $i++) {
		if(trim($csv[0][$i]) != '') {
			$expTable->execute(array('name' => $csv[0][$i])); //store the experiment name into the table experiments
			$expID = $db->lastInsertId(); //return the experiment ID
			$experiments[] = array('name' => $csv[0][$i], 'id' => $expID); //store the data into an array in order for the user to change the param.
			Upload::alterGeneTable($expID); //alter the experiment_gene table to add the experiment values

			if($count == 0) {Upload::insertGenes($csv, $expID);}

			$insertGeneSql = '';

			for($a = 0; $a < count($csv); $a++) {
				
			}
		}
	}
	

	function createSelectFormat($type, $id, $margin) {
		$result = '<label for="'.$type.'_'.$id.'" style="margin-right:'.$margin.'">'.$type.':</label><select name="'.$type.'_'.$id.'" id="'.$type.'_'.$id.'" style="min-width:200px;">';
		$result .= returnTypeWithOptionFormat($type);
		$result .= '</select><br/>';
		//return $result;
	}

	function returnTypeWithOptionFormat($type) {
		global $db;

		$optionString = '';
		$req = $db->query('SELECT * FROM parameters WHERE Type = "'.$type.'"');

		while($data = $req->fetch()) {
			$optionString .= '<option value="'.htmlspecialchars($data['Value']).'">'.htmlspecialchars($data['Value']).'</option>';
		}
		//return $optionString;
	}

	function createSpecifiedFormat($format, $type, $id ,$margin) {
		//return '<label for="'.$type.'_'.$id.'" style="margin-right:'.$margin.'">'.$type.':</label><input type="'.$format.'" name="'.$type.'_'.$id.'" id="'.$type.'_'.$id.'" /><br/>';
	}

?>
<br/>
			<form name="exp_param" id="exp_param">
				<?php
					for($i = 0; $i < count($experiments); $i++) {
						
						if(($i+1)%3 != 0 && ($i != count($experiments)-1)){
							?>
						<div class="float">
						<?php }
						else{
							?> <div> <?php
						}?>
						
						<!-- Experiment Name -->
						<span style="font-weight:bold;"><?= htmlspecialchars($experiments[$i]['name']); ?></span><br/>
						<!-- Experiment DataType -->
						<?= createSelectFormat('DataType', $i, '55px'); ?>
						<!-- Experiment CellType -->
						<?= createSelectFormat('CellType', $i, '64px'); ?>
						<!-- Readout -->
						<?= createSelectFormat('Readout', $i, '67px'); ?>
						<!-- Organism -->
						<?= createSelectFormat('Organism', $i, '57px'); ?>
						<!-- Receptor -->
						<?= createSelectFormat('Receptor', $i, '59px'); ?>
						<!-- Replicate -->
						<?= createSpecifiedFormat('number', 'Replicate', $i, '58px'); ?>
						<!-- Experimentalist -->
						<?= createSpecifiedFormat('text', 'Experimentalist', $i, '9px'); ?>
						<!-- Time -->
						<span style="margin-right:70px">Format:</span><span> 00:00:00</span><br/>
						<?= createSpecifiedFormat('time', 'Time', $i, '91px'); ?>
						<!-- Public -->
						<?= createSpecifiedFormat('checkbox', 'isPublic', $i, '67px'); ?>

						<br/>
						</div>
						<?php
					}
				?>
				<input type="submit" class="button" />
			</form>

	</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>
		<script type="text/javascript">
		  $('select').select2({
			  placeholder: "Select an option",
			  tags: true,
			  dropdownAutoWidth : true
			});
		</script>
	</body>
</html>
