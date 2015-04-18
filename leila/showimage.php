<?php
require_once 'variables.php';

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

if ($connection->connect_error) die($connection->connect_error);

$query = "SELECT * FROM objects WHERE ID = " . $_GET['ID'];
$result = $connection->query($query);

if (!$result) die ("Database query error" . $connection->error);

$result->data_seek(0);
$row = $result->fetch_array(MYSQLI_ASSOC);

header("Content-Type: " . $row['imagetype']);
if (isset($_GET['showthumb'])){
	echo $row['scaledimage'];
} else {
	echo $row['image'];
}
?>