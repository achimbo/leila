<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);

if (isset($_GET['objectid']) ){
	$searchid = sanitizeMySQL($connection, $_GET['objectid']);
	$query = "SELECT name FROM objects WHERE ID = '$searchid'";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	echo $row['name'];
}

if (isset($_GET['userid']) ){
	$searchid = sanitizeMySQL($connection, $_GET['userid']);
	$query = "SELECT firstname, lastname FROM users WHERE ID = '$searchid'";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	echo $row['firstname'] . " " . $row['lastname'];
}

if (isset($_GET['username']) ){
	$searchstring = sanitizeMySQL($connection, $_GET['username']);
	$query = "SELECT firstname, lastname FROM users WHERE (firstname LIKE '%$searchstring%') OR (lastname LIKE '%$searchstring%') ORDER BY lastname" ;
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;
	
	$mylist = [];
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
	
		$mylist[$r] = $row['firstname'] . " " . $row['lastname'];
	}
	echo json_encode($mylist);
}

if (isset($_GET['objectname']) ){
	$searchstring = sanitizeMySQL($connection, $_GET['objectname']);
	$query = "SELECT name FROM objects WHERE name LIKE '%$searchstring%' ORDER BY name" ;
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;

	$mylist = [];

	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		$mylist[$r] = $row['name'];
	}
	echo json_encode($mylist);
}