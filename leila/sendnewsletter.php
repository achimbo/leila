<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error) die ( $connection->connect_error );

if (isset ( $_POST ['sendnewsletter'] )) {
	$query = "SELECT email, firstname FROM users WHERE email != '' AND getsnewsletter = 1";
	
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;
	
	$subject = sanitizeMySQL ($connection, $_POST ['subject']);
	$text = sanitizeMySQL ($connection, $_POST ['text']);
	
	$headers = "From: $fromemail\r\n";
	$headers .= "Mime-Version: 1.0\r\n";
	$headers .= "Content-type: text/plain; charset=utf-8\r\n";
	$success = 0;
	$fail = 0;
	$failemail = "";
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
	
		$message = "Hallo {$row['firstname']} \n $text \n";
	
		if (mail($row['email'], $subject, $message, $headers)) {
			$success += 1;
		} else {
			$fail += 1;
			$failemail .= $failemail . $row['email'] . "<br>";
		}
	}
	
	$message = "<h1>Newsletter versand</h1>Erfolg: $success<br>Fehler: $fail <br>Misserfolg bei folgenden Email Adressen: <br> $failemail";
}

?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="leila.css" type="text/css">
<title>Member anzeigen</title>
</head>
<body>
	<?php include 'menu.php';?>
	<div id="content">
	<?php
	if (isset ( $message )) echo $message;
	?>
	
	<h1>Newsletter senden</h1>
		<form method="post" action="sendnewsletter.php">
			<label for="subject">Subject</label>
			<input type="text" name="subject" id="subject"><br>
			<label for="text">Text</label>
			<textarea name ="text" id="text" rows="10" cols="30"></textarea> <br>
			<input type="submit" name="sendnewsletter" value="Newsletter senden"><p>
		</form>
	
	</div>
</body>
</html>