<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

if ($connection->connect_error) die($connection->connect_error);

if (isset($_GET['ID']) ){
	$id = sanitizeMySQL($connection, $_GET['ID']);
} else {
	die("missing query");
}

$query = "SELECT * FROM objects WHERE ID = " . $id;
$result = $connection->query($query);

if (!$result) die ("Database query error" . $connection->error);

$result->data_seek(0);
$row = $result->fetch_array(MYSQLI_ASSOC);

header("Content-Type: " . $row['imagetype']);
header('Content-Disposition:filename="' . $row['imagename'] . '"');

if (isset($_GET['showthumb'])){
	if (isset($row['scaledimage'])){
		echo $row['scaledimage'];
	} else {
		echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
	}
} else {
	if (isset($row['image'])){
		echo $row['image'];
	} else {
		echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
	}}
?>