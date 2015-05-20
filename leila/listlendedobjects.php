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


$query = "SELECT o.name, u.firstname, u.lastname, r.loanedout, r.duedate FROM objects o
	INNER JOIN rented r ON o.ID = r.objects_ID INNER JOIN users u on r.users_ID = u.ID ";

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table class='rentedlist'>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	
	$mylist .= '<tr><td> Objektname ' .$row['name'] . ' Username ' . $row['firstname'] .  $row['lastname'] .
	' Von ' . $row['loanedout'] . ' Bis ' . $row['duedate'] . '</td></tr> ';
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

List lended Objects
<?= $mylist?>
</div>
</body>
</html>