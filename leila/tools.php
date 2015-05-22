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

// get the categories of object defined by $id
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

function isempty($name, $erroritem) {
	// check wether name is empty or does not contain alphabetic characters
	if (($name == "") || !preg_match("/[\w]/", $name)) return "$erroritem ist leer <br>";
	else return "";
}

function passwordvalid($password) {
	// 6 or more characters + special chars
	if (strlen($password) < 6 || !preg_match("/[^\w]/", $password)) {return false;} else {return true;};
}

function isint($int) {
	if (!is_numeric($int)) return "Kein g&uuml;ltiger Mitgliedsbeitrag";
	else return "";
}

function mycheckdate($date) {
	if ($date == "") {return "";}
	elseif (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date)) { return "";}
	else {	return "Datum ung&uuml;ltig <br>";}
}

function datepresent($date) {
	if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date)) { return "";}
	else {	return "Datum ung&uuml;ltig <br>";}
}

function datetimepresent($date) {
	if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}:?[0-9]{0,2}/", $date)) { return "";}
	else {	return "Datum oder Zeit ung&uuml;ltig <br>";}
}

function getfees($id) {
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT * FROM membershipfees WHERE users_ID = '$id'";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error " . $connection->error );
	
	$rows = $result->num_rows;	
	$feelist[] = NULL;
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$feelist[$r]['from'] = $row['from'];
		$feelist[$r]['until'] = $row['until'];
		$feelist[$r]['amount'] = $row['amount'];
	}
	$connection->close();
	return $feelist;
	
	
}

function getrentalsbyobject($id) {
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT loanedout, duedate, givenback, r.comment, firstname, lastname, u.ID AS userid FROM rented r INNER JOIN users u ON r.users_ID = u.ID WHERE r.objects_ID = '$id' ORDER BY loanedout ASC";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error " . $connection->error );
	
	$rows = $result->num_rows;
	$rentals[] = NULL;
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$rentals[$r]['loanedout'] = $row['loanedout'];
		$rentals[$r]['duedate'] = $row['duedate'];
		$rentals[$r]['givenback'] = $row['givenback'];
		$rentals[$r]['comment'] = $row['comment'];
		$rentals[$r]['firstname'] = $row['firstname'];
		$rentals[$r]['lastname'] = $row['lastname'];
		$rentals[$r]['userid'] = $row['userid'];
	}
	$connection->close();
	return $rentals;
}

function getrentalsbyuser($id) {
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT loanedout, duedate, givenback, r.comment, name AS objectname, o.ID AS objectid FROM rented r INNER JOIN objects o ON r.objects_ID = o.ID WHERE r.users_ID = '$id' ORDER BY loanedout ASC";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error " . $connection->error );

	$rows = $result->num_rows;
	$rentals[] = NULL;

	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$rentals[$r]['loanedout'] = $row['loanedout'];
		$rentals[$r]['duedate'] = $row['duedate'];
		$rentals[$r]['givenback'] = $row['givenback'];
		$rentals[$r]['comment'] = $row['comment'];
		$rentals[$r]['objectname'] = $row['objectname'];
		$rentals[$r]['objectid'] = $row['objectid'];
	}
	$connection->close();
	return $rentals;
}

function isvaliduser($id) {
	// -1 = wrong usertype 0 = invalid, 1 = valid for less than 6 weeks, 2 = valid longer than 6 weeks
	$valid = 0;
	
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT usertype FROM users WHERE ID = '$id'";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error " . $connection->error );
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	if ($row['usertype'] > 2) return -1;
	
	$fees = getfees($id);
	$now = date_create('now');
	foreach ($fees as $fee) {
		// getfees may return empty array
		if ($fee['from'] == NULL) return 0;
		$from = date_create($fee['from']);
		$until = date_create($fee['until']);
		$toend = date_diff($now, $until);
		$tobeginning = date_diff($from, $now);

		if ($tobeginning->format('%R%a') >= 0 && $toend->format('%R%a') > 42) {
			return 2;
		} elseif ($tobeginning->format('%R%a') >= 0 && $toend->format('%R%a') < 42 && $toend->format('%R%a') >= 0) {
			$valid = 1;
		}
	}
	return $valid;
}

// return -1 - wrong status, 0 rented away, 1 available
function objectisavailable($id) {
	$available = 0;
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);

	$query = "SELECT isavailable FROM objects WHERE ID = '$id'";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error " . $connection->error );
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	if ($row['isavailable'] > 1) return -1;
	
	// $query = "SELECT loanedout, duedate, givenback, isavailable FROM rented r INNER JOIN objects o ON r.objects_ID = o.ID WHERE o.ID = '$id'";
	$query = "SELECT givenback FROM rented WHERE objects_ID = '$id'";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error " . $connection->error );

	$rows = $result->num_rows;
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		if ($row['givenback'] == NULL) return 0	;	
	}
	return 1;
}

?>