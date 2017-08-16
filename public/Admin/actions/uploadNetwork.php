<!DOCTYPE html>
<html>
	<head>
		<title>Upload Network</title>
	</head>
	<body>
        <h1>Upload Network</h1>
<?php

	require '../../../app/includes.php';

	//if not logged in
	if(!isset($_SESSION['User']['isAuth']) || !$_SESSION['User']['isAuth'] == 1) {
		header('Location: ../experiments.php');
	}

	$err = array();


	if(isset($_FILES['file2'])) {
		$network = new NetworkUpload('file2');
		if(!$network->isValid()) {
			$errors = $network->getErrors();
			?>
			<div id="errors">
                <span style="color:red;font-size:15px;font-weight: bold;">Errors have occurred: (Nothing has been uploaded)</span><br/>
				<?php foreach($errors as $error) {?>
				<span style="color:red;"><?= $error ?></span><br/>
				<?php  }?>
			</div>

			<?php
		} else if(count($network->getWarnings()) > 0) {
			$warnings = $network->getWarnings();
			?>
			<div id="warnings">
				<span style="color:orange;">Warnings have occurred: (Upload of all other rows has still occurred) </span>
				<?php foreach($warnings as $warning) {?>
				<span style="color: orange;"><?php if(is_array($warning)) { print_modified_r($warning); } else { echo $warning; } ?></span><br/>
				<?php } ?>
			</div>
		<?php
		} else {
			echo '<span style="color: green;">Successful</span>';
		}


	}


		/*$array = Upload::handleUpload('file2');

		$name = explode('_', $_FILES['file2']["name"]);
		$name = $name[0];

		$headerNo = count($array[0]);
		if($headerNo < 4) {
			$err[] = 'All columns are required.';
		}
		for($i= 1; $i < count($array); $i++) {
			if(count($array[$i]) != $headerNo) {
				$err[] = 'Invalid. Missing a value. Blank fields are not allowed. On line '.($i+1).'. Make sure to not have an extra empty line in the file';
			}
		}
		$skipFirstLine = false;
		if(strtolower($array[0][2]) == 'genea' && strtolower($array[0][3]) == 'geneb') {
			$skipFirstLine = true;
		}

		//print_r($err);
		if(count($err) == 0) {
			$where = File::createWhereQuery(count($array), $headerNo+2, $skipFirstLine, TRUE);
			$values = File::createWhereValues($array, $name, $skipFirstLine);
			echo count($values).PHP_EOL;
			echo 'If these values do not match then there is a problem and the data was not uploaded. Please make sure that max_allowed_packet is somewhat large enough to run one huge query.'.PHP_EOL;

			try {
				$req = $db->prepare('INSERT INTO Network(Organism, Type, EntrezID_B, EntrezID_A, GeneA, GeneB) '.$where);
				$req->execute($values);
			} catch (Exception $e) {
				echo 'Exception: '.var_dump($e->getMessage());
			}

		}

	}

	if(count($err) > 0) {
		echo '<span style="color:red;">'.implode('<br/>', $err).'</span>';
	} else {
		echo '<span style="color:green;">Success</span>';
	}
?>
		<br/>
		<a href="../home.php">Click here to go back</a>
	</body>
</html>*/

