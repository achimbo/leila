<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

$mylist = '';
$sortmax = sanitizeMySQL($connection, $_GET['sortmax']);
$sortmax = $sortmax != "" ? $sortmax : 10;

$from = sanitizeMySQL($connection, $_GET['datefrom']);
$until = sanitizeMySQL($connection, $_GET['dateuntil']);


if(isset($_GET['byuser'])) {
	if (datepresent($from) == "" && datepresent($until) == "") {
		$query = "SELECT u.firstname, u.lastname, r.user_id, COUNT(r.user_id) AS timesrented
		FROM rented r JOIN users u ON r.user_id = u.user_id
		WHERE loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE)
		GROUP BY user_id
		ORDER BY timesrented DESC
		LIMIT $sortmax";
	} else {
		$query = "SELECT u.firstname, u.lastname, r.user_id, COUNT(r.user_id) AS timesrented
		FROM rented r JOIN users u ON r.user_id = u.user_id
		GROUP BY user_id
		ORDER BY timesrented DESC
		LIMIT $sortmax";
	}
	$result = $connection->query($query);
	if (!$result) die ("Database query error " . $connection->error);
	$rows = $result->num_rows;
	
	$mylist .= "<table id='toplist'>";
	$mylist .= "<tr><th>User</th><th>Geliehen</th></tr>";
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
	
		$mylist .= '<tr><td><a href="editmember.php?ID=' . $row["user_id"] . '">' .$row['firstname'] . ' ' . $row['lastname'] . '</a></td>'
				. '<td>' . $row['timesrented'] . '</td></tr> ' . "\n";
	}
	
	$mylist .= "</table>";
	
} elseif(isset($_GET['byobject'])) {
	if (datepresent($from) == "" && datepresent($until) == "") {
		$query = "SELECT o.name, r.object_id, COUNT(r.object_id) AS timesrented
		FROM rented r JOIN objects o ON r.object_id = o.object_id
		WHERE loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE)
		GROUP BY object_id
		ORDER BY timesrented DESC
		LIMIT $sortmax";
	} else {
		$query = "SELECT o.name, r.object_id, COUNT(r.object_id) AS timesrented
		FROM rented r JOIN objects o ON r.object_id = o.object_id
		GROUP BY object_id
		ORDER BY timesrented DESC
		LIMIT $sortmax";
	}
	$result = $connection->query($query);
	if (!$result) die ("Database query error " . $connection->error);
	$rows = $result->num_rows;
	
	$mylist .= "<table id='toplist'>";
	$mylist .= "<tr><th>Objekt</th><th>Verliehen</th></tr>";
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
	
		$mylist .= '<tr><td><a href="showobject.php?ID=' . $row["object_id"] . '">' .$row['name'] . '</a></td>'
				. '<td>' . $row['timesrented'] . '</td></tr> ' . "\n";
	}
	
	$mylist .= "</table>";
}
?>


<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="leila.css" type="text/css">
<title>Verleih &Uuml;bersicht</title>
</head>
<body>
<?php include 'menu.php';?>
<div id="content">
<h1>Statistik</h1>
<form>
	<label for="datefrom">Datum Von: </label>
	<input type="text" id="datefrom" name="datefrom" value=<?php echo $from ?>><br>
	<label for="dateuntil">Datum Bis: </label>
	<input type="text" id="dateuntil" name="dateuntil" value=<?php echo $until ?>>	<br>
	<label for="sortmax">Maximale Eintr&auml;ge </label>
	<input type="text" id="sortmax" name="sortmax" value=<?php if (isset($_GET['sortmax'])){echo $_GET['sortmax'];} else {echo 10;}?>>	<br>
	<input type="submit" name="byuser" value="nach User">
	<input type="submit" name="byobject" value="nach Objekt">
</form><p>
<?= $mylist?>

</div>
</body>
</html>