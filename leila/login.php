<?php
require_once 'variables.php';
require_once 'tools.php';

if (isset($_POST['username'])) {
	$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
	if ($connection->connect_error) die ( $connection->connect_error );
	
	$username = sanitizeMySQL($connection, $_POST['username']);
	$submittedpass = sanitizeMySQL($connection, $_POST['password']);

	$query = "SELECT password FROM users WHERE usertype = 1 AND lastname = '$username'";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$submittedpass = hash ( 'ripemd160', "$salt$submittedpass" );
	if ($row['password'] == $submittedpass) {
		$loginsuccess = true;
		session_start();
		session_regenerate_id();
		$_SESSION['username'] = $username;
		$_SESSION['usertype'] = "admin";
	} else {
		$loginsuccess = false;
	}
}
	?>
	
<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<link rel="stylesheet" href="leila.css" type="text/css">
</head>
<body>
<?php

if (isset($loginsuccess) && $loginsuccess){
	echo <<<_END
	<h1>Login erfolgreich</h1>
	<script type="text/javascript">
	function leave() {
	  window.location = "listobjects.php";
	}
	setTimeout("leave()", 3000);
	</script>
_END;
} elseif (isset($loginsuccess) && !$loginsuccess) {
	echo <<<_END
	<h1>Login fehlgeschlagen</h1>
	<script type="text/javascript">
	function leave() {
	  window.location = "login.php";
	}
	setTimeout("leave()", 3000);
	</script>
_END;
} elseif (isset($_GET['logout']) ) {
	session_start();
	$_SESSION = array();
	setcookie(session_name(), '', time() - 200000, '/');
	session_destroy();
	echo "Abgemeldet, wieder <a href='login.php'>anmelden</a>";
} else {
echo <<<_END
<h1>Bitte einloggen</h1>
<form method="post" action="login.php">
	<label for="username">Username</label>
	<input type="text" id="username" name="username"><br>
	<label for="password">Passwort</label>
	<input type="password" id="password" name="password"><br>
	<input type="submit" value="login">
</form>
_END;
}

?>
</body>
</html>