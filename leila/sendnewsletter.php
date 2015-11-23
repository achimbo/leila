<?php
require_once 'variables.php';
require_once 'tools.php';
require_once 'phpmailer/PHPMailerAutoload.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error) die ( $connection->connect_error );

if (isset ( $_POST ['sendnewsletter'] )) {
	$query = "SELECT email, firstname, lastname FROM users WHERE email != '' AND getsnewsletter = 1";
	
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;
	
	$subject = sanitizeMySQL ($connection, $_POST ['subject']);
	$text = $_POST ['text'];
	
	$mail = new PHPMailer;
	$mail->setFrom("$fromemail", "$fromname");
	$mail->Subject = "$subject";
	
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
	
		$mail->Body = "Hallo {$row['firstname']} \n $text \n";
		$mail->addAddress($row['email'], $row['firstname'] . " " . $row['lastname']);
		$mail->addAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
	
		if ($mail->send()) {
			$success += 1;
		} else {
			$fail += 1;
			$failemail .= $failemail . $row['email'] . "<br>";
		}
		$mail->clearAddresses();
		$mail->clearAttachments();
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
	if (isset ( $message )) echo $message;	?>
	
	<h1>Newsletter senden</h1>
		<form method="post" action="sendnewsletter.php" enctype="multipart/form-data">
			<label for="subject">Subject</label>
			<input type="text" name="subject" id="subject"><br>
			<label for="text">Text</label>
			<textarea name ="text" id="text" rows="10" cols="30"></textarea> <br>
			<label for="attachment">Anhang</label> <input id="attachment" type="file" name="attachment"> <br>
			<input type="submit" name="sendnewsletter" value="Newsletter senden"><p>
		</form>
	
	</div>
</body>
</html>