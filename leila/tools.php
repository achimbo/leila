<?php
function sanitizeString($var){
	$var = stripslashes($var);
	$var = htmlentities($var);
	$var = strip_tags($var);
	return $var;
}

function sanitizeMySQL($connection, $var){
	$var = $connection->real_escape_string($var);
	$var = sanitizeString($var);
	return $var;
}

function gettopcategories(){
	include 'login.php'; // require funktioniert nicht ???
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

	if ($connection->connect_error) die($connection->connect_error);

	$query = "SELECT * FROM categories WHERE ischildof IS NULL";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;

	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		if (isset($_POST['getsubcategories']) && $row['ID'] == $_POST['topcategory']){
			echo  '<option selected="selected" value="' .$row['ID'] . '">' . $row['name'] . '</option>';
		} else {
			echo '<option value="' .$row['ID'] . '">' . $row['name'] . '</option>';
		}
	}
	$connection->close();
	return;
}

function getsubcategories($subcatid){
	include 'login.php'; // require funktioniert nicht ???
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

	if ($connection->connect_error) die($connection->connect_error);

	$subcatid = sanitizeString($subcatid);
	$subcatid = sanitizeMySQL($connection, $subcatid);

	$query = "SELECT * FROM categories WHERE ischildof = $subcatid";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;

	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		echo '<option value="' .$row['ID'] . '">' . $row['name'] . '</option>';
	}
	$connection->close();
	return;
}
?>