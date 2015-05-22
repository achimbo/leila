<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

$message = "";
$mylist = '';

if (isset($_GET['showoverdue'])) {
	$query = "SELECT o.ID AS objectid, o.name, u.ID AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.ID = r.objects_ID INNER JOIN users u on r.users_ID = u.ID 
			WHERE DATEDIFF(duedate, curdate()) < 0  AND givenback IS NULL";
	$message = "die &uuml;berzogen sind";
} elseif (isset($_GET['showrented'])) {
	$query = "SELECT o.ID AS objectid, o.name, u.ID AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.ID = r.objects_ID INNER JOIN users u on r.users_ID = u.ID
			WHERE givenback IS NULL ";	
	$message = "die gerade verliehen sind";
} elseif (isset($_GET['datefrom']) && isset($_GET['dateuntil'])) {
	$from = sanitizeMySQL($connection, $_GET['datefrom']);
	$until = sanitizeMySQL($connection, $_GET['dateuntil']);
	if (datepresent($from) == "" && datepresent($until) == "") {
	$query = "SELECT o.ID AS objectid, o.name, u.ID AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.ID = r.objects_ID INNER JOIN users u on r.users_ID = u.ID
					WHERE loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ";	
	$message = "die zwischen $from und $until verliehen wurden";
	} else {
		$query = "SELECT o.ID AS objectid, o.name, u.ID AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.ID = r.objects_ID INNER JOIN users u on r.users_ID = u.ID ";
		$error = "ung&uuml;ltiges Datum";
	}
} else {$query = "SELECT o.ID AS objectid, o.name, u.ID AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.ID = r.objects_ID INNER JOIN users u on r.users_ID = u.ID ";}

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table id='rentedlist'>";
$mylist .= "<caption>Verleihliste</caption>";
$mylist .= "<thead><tr><th>Objekt</th><th>User</th><th>Von</th><th>Bis</th><th>Zur&uuml;ck</th></tr></thead>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	
	$mylist .= '<tr><td><a href="showobject.php?ID=' . $row["objectid"] . '">' .$row['name'] . '</a></td>'
			. '<td><a href="editmember.php?ID=' . $row['userid'] . '">' . $row['firstname'] . ' ' .  $row['lastname'] . '</a></td>'
	. '<td><a href="lendobject.php?edit=1&objectid=' . $row['objectid'] . '&userid=' . $row['userid'] . '&loanedout=' . $row['loanedout'] . '">' . $row['loanedout']  . '</a></td>' 
	. '<td>' . $row['duedate'] . '</td><td>' . $row['givenback'] . '</td></tr> ';
	//$mylist .= 'Description ' . $row['description'] . '<br>';
}

$mylist .= "</table>";
?>

<html>
<head>
<link rel="stylesheet" href="leila.css" type="text/css">
<title>Verleih &Uuml;bersicht</title>
</head>
<body>
<?php include 'menu.php';?>
<div id="content">
<h3>Verliehene Objekte <?=$message?></h3>
<?php if (isset ( $error ) && $error != "") echo "<span class='errorclass'>Fehler: $error </span>" ?>
<form method="get" action="listlendedobjects.php">
	<label for="datefrom">Datum Von: </label>
	<input type="text" id="datefrom" name="datefrom"><br>
	<label for="dateuntil">Datum Bis: </label>
	<input type="text" id="dateuntil" name="dateuntil">	
	<input type="submit" value="Suchen">
</form>
<form>
	<input type="submit" name="showrented" value="Verliehene anzeigen">
	<input type="submit" name="showoverdue" value="&Uuml;berzogene anzeigen">
</form>
<?= $mylist?>
</div>
</body>
</html>