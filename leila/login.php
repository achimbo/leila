<?php
require_once 'variables.php';
require_once 'tools.php';
require_once('configlocale.php');

if (isset($_POST['username'])) {
	$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
	if ($connection->connect_error) die ( $connection->connect_error );
	
	$username = sanitizeMySQL($connection, $_POST['username']);
	$submittedpass = sanitizeMySQL($connection, $_POST['password']);

	$query = "SELECT password FROM users WHERE usertype = 1 AND email = '$username'";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$submittedpass = hash ( 'ripemd160', "$salt$submittedpass" );
	if ($row['password'] == $submittedpass) {
		$loginsuccess = true;
		session_start();
		// prevent session fixation
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
    <link rel="stylesheet" href="leila-new.css"  type="text/css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  type="text/css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css" type="text/css">
      <meta charset="utf-8"/>
    <title><?= _('login')?></title>
</head>
<body>
<div class='container'>
    <div class="col-md-6">
<?php

if (isset($loginsuccess) && $loginsuccess){
	echo "<h1>" . _('login successfull') . "</h1>";
    echo "<br><a href='listobjects.php'>" . _('list objects') . "</a>";
    echo <<<_END
	<script type="text/javascript">
	function leave() {
	  window.location = "listobjects.php";
	}
	setTimeout("leave()", 3000);
	</script>
_END;
} elseif (isset($loginsuccess) && !$loginsuccess) {

    echo "<h1>" . _('login failed') . "</h1>";
	echo <<<_END
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
	echo "<h1>" . _('logged out') . " <br><a href='login.php'>" . _('login again') . "</a></h1>";
} else {
    echo "<h1>" . _('please login') . "</h1>";
echo <<<_END
<form method="post" action="login.php">
    <div class="form-group">
	<label for="username">Username</label>
	<input class="form-control" type="text" id="username" name="username">
	</div>
	<div class="form-group">
	<label for="password">Password</label>
	<input type="password" class="form-control" id="password" name="password"><br>
	<input type="submit" class="btn btn-default" value="login">
</form>
_END;
}

?>
        </div>
    </div>
</body>
</html>