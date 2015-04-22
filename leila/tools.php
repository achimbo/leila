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
	include 'variables.php'; // require funktioniert nicht ???
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

function getcategoriesaslinks(){
	include 'variables.php'; // require funktioniert nicht ???
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
		
	if (isset($_GET['catid'])) {
		$catid = sanitizeMySQL($connection, $_GET['catid']);
	} else {
		$catid = '0';
	}
	

	$query = "SELECT * FROM categories WHERE ischildof IS NULL";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;

	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		if ($row['ID'] == $catid || $row['ID'] == getparentid($catid)){
			echo  '<b><a href="listobjects.php?catid=' .$row['ID'] . '">' . $row['name'] . ' </a></b>&nbsp;';
		} else {
			echo '<a href="listobjects.php?catid=' .$row['ID'] . '">' . $row['name'] . ' </a>&nbsp;';
		}
		
	}
	if(!istopcategory($catid)){	
		echo "<br>";	
		getsiblings($catid);
	} else {
		echo "<br>";
		getkids($catid);
	}
	
	$connection->close();
	return;
}

function getparentid($catid){
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT * FROM categories WHERE id = $catid";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	return $row['ischildof'];	
}

function istopcategory($catid){
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$catid = sanitizeMySQL($connection, $catid);
	$query = "SELECT * FROM categories WHERE id = $catid";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	if ($row['ischildof'] == NULL){
		return true;
	} else {
		return false;
	}
	
}

function getkids($catid){
	include 'variables.php'; // require funktioniert nicht ???
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

	if ($connection->connect_error) die($connection->connect_error);

	$catid = sanitizeMySQL($connection, $catid);

	$query = "SELECT * FROM categories WHERE ischildof = $catid";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;

	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		echo '<a href="listobjects.php?catid=' .$row['ID'] . '">' . $row['name'] . ' </a>&nbsp;';
	}
	$connection->close();
	return;
}

function getsiblings($catid){
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$catid = sanitizeMySQL($connection, $catid);
	$query = "SELECT * FROM categories WHERE id = $catid";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$parentid = $row['ischildof'];
	$query = "SELECT * FROM categories WHERE ischildof = $parentid";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
	
		if ($row['ID'] == $catid){
			echo  '<b><a href="listobjects.php?catid=' .$row['ID'] . '">' . $row['name'] . ' </a></b>&nbsp;';
		} else {
			echo '<a href="listobjects.php?catid=' .$row['ID'] . '">' . $row['name'] . ' </a>&nbsp;';
		}
	}
	$connection->close();
	return;
	
	
}

// get subcategories of category $subcatid
function getsubcategories($subcatid){
	include 'variables.php'; // require funktioniert nicht ???
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

	if ($connection->connect_error) die($connection->connect_error);

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

// get the categories ob object defined by $id
function getcategories($id){
	include 'variables.php'; 
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$id = sanitizeMySQL($connection, $id);
	
	$query = "SELECT o.ID AS oid, o.name AS oname, c.ID AS catid, c.name AS catname, c.ischildof FROM objects o
		INNER JOIN objects_has_categories ohc ON o.ID = ohc.objects_ID
		INNER JOIN categories c on ohc.categories_ID = c.ID 
		WHERE o.ID = $id";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;
	
	$categories[] = NULL;
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$categories[$r]['name'] = $row['catname'];
		$categories[$r]['catid'] = $row['catid'];	
		$childof = $row['ischildof'];
		if ($childof != NULL){
			$query2 = "SELECT * FROM categories WHERE id = $childof";
			$result2 = $connection->query($query2);	
			if (!$result) die ("Database query error" . $connection->error);
			$result2->data_seek(0);
			$row2 = $result2->fetch_array(MYSQLI_ASSOC);
			$categories[$r]['name'] = $row2['name'] . " - " . $categories[$r]['name'];
		}
	}
	$connection->close();	
	return $categories;
}

// return name of category $id
function getcategoryname($id){
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$id = sanitizeMySQL($connection, $id);
	$query = "SELECT * FROM categories WHERE ID = $id";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	return $row['name'];
	$connection->close();
}

function getcurrentdate(){
	return date("Y-m-d", time());
}

function addquotes($mystring){
	return $mystring = "'" . $mystring . "'";
}

function checkname($name) {
	if (preg_match("/[^\w- ]/", $name)) return "Nur Buchstaben, Ziffern und Bindestriche in Namen <br>";
	elseif ($name == "") return "Name leer <br>";
	else return "";
}

function mycheckdate($date) {
	if ($date == "") {return "";}
	elseif (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date)) { return "";}
	else {	return "Datum ung&uuml;ltig <br>";}
}

?>