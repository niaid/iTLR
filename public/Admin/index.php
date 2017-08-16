<?php
	/********************
	**  Log In Page
	********************/
	require_once '../../app/includes.php'; //require the website foundation

	
	//Give notice if the user meant to log out
	if(isset($_GET['logout']) && isset($_SESSION['User']['isAuth']) && $_SESSION['User']['isAuth'] == 1) {
		$formInfo = 'You have been succesfully logged out';
	}

	//User Log Out Always when visiting this page
	$_SESSION['User']['isAuth'] = 0;

	//REQUEST LOG IN SENT IN HEADER
	if(isset($_POST['username']) && isset($_POST['password'])) {
		//validation of username and password
		if($_POST['username'] == getenv('ADMIN_USER') && $_POST['password'] == getenv('ADMIN_PASS')) {
			$_SESSION['User']['isAuth'] = 1;
			header('Location: home.php');
		}
		else {
			$formError = 'Incorrect Username and/or Pass';
		}

	}
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Log In</title>
		<style type="text/css">
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
                border: 3px solid #3498db; 
                color: #3498db; 
            }
            body{
            	background-color: gainsboro;
                font-family: "Comic Sans MS";
            }
            .login{
            	padding:15px;
                border-radius: 15px 15px 15px 15px;
                box-shadow:-1px 1px 1px rgba(0,0,0,0.15);
                background:#fff;
                border: 2px solid #3498db;
                width: 261;
                margin-right: auto;
                margin-left: auto;
                width: 260px;
            }
		</style>
	</head>
	<body>
		<div class="login">
			<p style="color:green;"><?= (isset($formInfo)) ? $formInfo : ''; ?>
			<p style="color:red"><?= (isset($formError)) ? $formError : ''; ?>
			<!-- LOGIN FORM -->
			<form action="index.php" method="POST" name="login" id="login">
				<label for="username" style="margin-right:5px">Username:</label><input type="text" name="username" id="username"/><br/>
				<label for="password" style="margin-right:11px">Password:</label><input type="password" name="password" id="password"/><br/>
				<input type="submit" class="button" value="Log In"><br/>
			</form>
			<!-- END OF LOGIN FORM -->
		</div>
	</body>
</html>