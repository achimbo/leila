<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$mylist = '';
$message = '';

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);

if (isset($_GET['searchstring'])){
	$searchstring = sanitizeMySQL($connection, $_GET['searchstring']);
	$query = "SELECT * FROM leila.users WHERE (firstname LIKE '%$searchstring%') OR (lastname LIKE '%$searchstring%') ORDER BY lastname";
	$message = "mit Namen " . $searchstring;
} elseif (isset($_GET['searchid'])) {
	$searchid = sanitizeMySQL($connection, $_GET['searchid']);
	$query = "SELECT * FROM users WHERE ID = '$searchid'";
	$message = "mit ID " . $searchid;
} else {
	$query = "SELECT * FROM users ORDER BY lastname";
}

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table class='memberlist'>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$mylist .= "<tr><td><a href='editmember.php?ID=" . $row['ID'] . "'>" . $row['firstname'] . " " . $row['lastname'] . "</a></td></tr>";
	
	// $mylist .= "<tr><td> Name <a href='showmember.php?ID=' .$row['ID'] > $row['firstname'] . </a></td></tr> ";
	//$mylist .= 'Description ' . $row['description'] . '<br>';
}

$mylist .= "</table>";
?>

<!DOCTYPE html>
<html>
<head>
   <link rel="stylesheet" href="leila.css" type="text/css">
</head>
<body>
<?php include 'menu.php';?>
<div id="content">

<h3>Mitglieder suchen</h3>
<form method="get" action="listmembers.php">
	<label for="searchstring">In Namen suchen:</label> 
	<input type="text" id="searchstring" name="searchstring">
	<input type="submit" value="Suchen">
</form>
<form method="get" action="listmembers.php">
	<label for="searchid">In ID suchen: </label>
	<input type="text" id="searchid" name="searchid">
	<input type="submit" value="ID suchen">
</form>
	
<h3>Mitglieder <?= $message?></h3>
<?= $mylist?>
</div>

</body>
</html>