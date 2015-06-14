<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

$feesum = "";
$mylist = "";

if (isset($_GET['datefrom']) && isset($_GET['dateuntil'])) {
	$from = sanitizeMySQL($connection, $_GET['datefrom']);
	$until = sanitizeMySQL($connection, $_GET['dateuntil']);
	if (datepresent($from) == "" && datepresent($until) == "") {

		$query = "SELECT COUNT(*) AS count FROM membershipfees WHERE membershipfees.from BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ";
		$result = $connection->query($query);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$count = $row['count'];
		$pag = paginate($count);		
		
		$query = "SELECT SUM(amount) as amount FROM membershipfees WHERE membershipfees.from BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ";
		$result = $connection->query($query);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$feesum = "Summe im Zeitraum zwischen $from und $until: " . $row['amount'] . " Euro";
		
		$message = "die zwischen $from und $until verliehen wurden";
		$query = "SELECT mf.*, u.firstname, u.lastname FROM membershipfees mf INNER JOIN users u ON mf.user_id = u.user_id WHERE mf.from BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ORDER BY mf.from ASC " . $pag['query'];
	} else {
		$error = "Datum fehlerhaft";
	}
} else {
	$query = "SELECT COUNT(*) AS count FROM membershipfees";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	
	$query = "SELECT SUM(amount) as amount FROM membershipfees";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$feesum = "Summe insgesamt: " . $row['amount'] . " Euro";
	
	$message = "insgesamt";
	$query = "SELECT mf.*, u.firstname, u.lastname FROM membershipfees mf INNER JOIN users u ON mf.user_id = u.user_id ORDER BY mf.from ASC " . $pag['query'];
}

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table id='feelist'>";
$mylist .= "<caption>Geb&uuml;hrenliste</caption>";
$mylist .= "<thead><tr><th>Benutzer</th><th>Von</th><th>Bis</th><th>Betrag</th></tr></thead>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$mylist .= "<tr><td><a href='editmember.php?ID=" . $row['user_id'] . "'>" . $row['firstname'] . " " . $row['lastname'] . "</a></td>"
			. "<td>". $row['from'] . "</td><td>" . $row['until'] . "</td><td>" . $row['amount'] . "</td></tr>\n";
}
$mylist .= "</table>";

?>



<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="leila.css" type="text/css">
<title>Geb&uuml;hren &Uuml;bersicht</title>
</head>
<body>
<?php include 'menu.php';?>
<div id="content">
<h1>Geb&uuml;hren</h1>
<?php if (isset ( $error ) && $error != "") echo "<span class='errorclass'>Fehler: $error </span>" ?>
<form method="get" action="listfees.php">
	<label for="datefrom">Datum Von: </label>
	<input type="text" id="datefrom" name="datefrom"><br>
	<label for="dateuntil">Datum Bis: </label>
	<input type="text" id="dateuntil" name="dateuntil">	
	<input type="submit" value="Suchen">
</form><p>

<h3><?= $feesum ?></h3><p>
<?= $mylist ?>
<?= $pag['footer']?>

</div>
</body>
</html>