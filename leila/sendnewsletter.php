<?php
require_once 'variables.php';
require_once 'tools.php';
require_once 'phpmailer/PHPMailerAutoload.php';

session_start();
require_once('configlocale.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die (_("please <a href='login.php'>login</a>"));

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

		$mail->Body = _("hello") . " {$row['firstname']} \n $text \n";
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

	$message = "<h1>" . _("sent newsletter") . "</h1>" . _("success: ") . " $success <br>" . _("error:") . " $fail <br>" . _("the following addresses failed") . "<br> $failemail";
}

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="leila-new.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css" type="text/css">
	<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">
	<script src="jquery/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="jquery-ui/jquery-ui.min.js"></script>

	<meta charset="utf-8"/>
	<title><?= _('send newsletter')?></title>
</head>

<body>
<div class="container">

	<?php include 'nav.php';?>

	<script type="text/javascript">
		document.getElementById('memberstab').className = 'active';
		document.getElementById('objectspane').className = 'tab-pane';
		document.getElementById('memberspane').className = 'tab-pane active';
	</script>

	<?php
	if (isset ( $message ) && $message != "") {
		echo "<div class='alert alert-success'>" . $message . " </div><p>";
	}
	?>

	<h1><?= _('send newsletter')?></h1>
	<div class="row margin-top">
		<div class="col-md-6">
			<form method="post" action="sendnewsletter.php" enctype="multipart/form-data">
				<div class="form-group">
					<label for="subject"><?= _('subject')?></label>
					<input type="text" class="form-control" name="subject" id="subject"><br>
					<label for="text"><?= _('text')?></label>
					<textarea name ="text" class="form-control" id="text" rows="10" cols="30"></textarea> <br>
					<label for="attachment"><?= _('attachment')?></label> <input id="attachment" type="file" name="attachment"> <br>
					<input type="submit" class="btn btn-default" name="sendnewsletter" value="<?= _('send newsletter')?>"><p>
				</div>
			</form>
		</div>
	</div>
</div>
</body>
</html>