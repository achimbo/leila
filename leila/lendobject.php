<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

$message = "";

if (isset($_POST['lendobject'])) {
	$userid = sanitizeMySQL ( $connection, $_POST ['userid'] );
	$objectid = sanitizeMySQL ( $connection, $_POST ['objectid'] );
	$loanedout = sanitizeMySQL ( $connection, $_POST ['loanedout'] );
	$duedate = sanitizeMySQL ( $connection, $_POST ['duedate'] );
	$comment = sanitizeMySQL ( $connection, $_POST ['comment'] );
	
	$error = isempty($userid, "User ID");
	$error .= isempty($objectid, "Objekt ID");
	$error .= datetimepresent($loanedout);
	$error .= datepresent($duedate);
	
	if (isvaliduser($userid) < 1) {
		$error .= "User ist ung&uuml;ltig";
	}
	
	if (objectisavailable($objectid) == 0) $error .= "Objekt bereits verliehen";
	if (objectisavailable($objectid) == -1) $error .= "Objekt Status falsch";
	// wenn loanedout in der Zukunft Abbruch?
	
	if ($error == "") {
		$query = "INSERT INTO rented (objects_ID, users_ID, loanedout, duedate, comment) 
			VALUES ('$objectid', '$userid', '$loanedout', '$duedate', '$comment') ";
		$result = $connection->query ( $query );
		
		if (! $result) {
			die ( "Angaben fehlerhaft" . $connection->error );
		} else {
			$message = "Verleihvorgang Gespeichert<p>";
		}
	}
	
}

?>

<html>
<head>
<link rel="stylesheet" href="leila.css" type="text/css">
<title>Objekt verleihen</title>
</head>
<body>
<?php include 'menu.php';?>
<div id="content">
	<h1>Objekt verleihen</h1>
	<?php
	if (isset ( $error ) && $error != "")
		echo "<div class='errorclass'>Fehler: $error </div><p>";
	if (isset ( $message ))
		echo $message;
	?>
	<form action="lendobject.php" method="post">
	<label for="userid">User ID</label>
	<input type="text" name="userid" id="userid" <?php if (isset($_GET['userid'])) {echo "value='" . $_GET['userid']. "'";}?>>
	<input type="text" disabled="disabled" name="firstname" id="firstname">
	<input type="text" disabled="disabled" name="lastname" id="lastname"><p>
	<label for="objectid">Objekt ID</label>
	<input type="text" name="objectid" id="objectid" <?php if (isset($_GET['objectid'])) {echo "value='" . $_GET['objectid']. "'";}?>>
	<input type="text" disabled="disabled" name="objectname" id="objectname"><p>
	<label for="loanedout">Von</label>
	<input type="text" name="loanedout" id="loanedout" value="<?= date("Y-m-d G:i:s", time())?>"><p>
	<label for="duedate">Bis</label>
	<input type="text" name="duedate" id="duedate" value="<?= date("Y-m-d", (time() + 60 * 60 * 24 * 14))?>"><p>
	<label for="comment">Kommentar</label>
	<textarea name="comment" id="comment"></textarea><p>
	<input type="submit" name="lendobject" value="objekt verleihen">
	</form>
</div>
</body>
</html>