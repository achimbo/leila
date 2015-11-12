<?php
function sanitizeString($var){
	$var = stripslashes($var);
	$var = htmlspecialchars($var);
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

		if (isset($_POST['getsubcategories']) && $row['category_id'] == $_POST['topcategory']){
			echo  '<option selected="selected" value="' .$row['category_id'] . '">' . $row['name'] . '</option>';
		} else {
			echo '<option value="' .$row['category_id'] . '">' . $row['name'] . '</option>';
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

		if ($row['category_id'] == $catid || $row['category_id'] == getparentid($catid)){
			echo  '<b><a href="listobjects.php?catid=' .$row['category_id'] . '">' . $row['name'] . ' </a></b>&nbsp;';
		} else {
			echo '<a href="listobjects.php?catid=' .$row['category_id'] . '">' . $row['name'] . ' </a>&nbsp;';
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
	$query = "SELECT * FROM categories WHERE category_id = $catid";
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
	$query = "SELECT * FROM categories WHERE category_id = $catid";
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

		echo '<a href="listobjects.php?catid=' .$row['category_id'] . '">' . $row['name'] . ' </a>&nbsp;';
	}
	$connection->close();
	return;
}

function getsiblings($catid){
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$catid = sanitizeMySQL($connection, $catid);
	$query = "SELECT * FROM categories WHERE category_id = $catid";
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
	
		if ($row['category_id'] == $catid){
			echo  '<b><a href="listobjects.php?catid=' .$row['category_id'] . '">' . $row['name'] . ' </a></b>&nbsp;';
		} else {
			echo '<a href="listobjects.php?catid=' .$row['category_id'] . '">' . $row['name'] . ' </a>&nbsp;';
		}
	}
	$connection->close();
	return;
	
	
}

// get subcategories of category $topcatid
function getsubcategories($topcatid){
	include 'variables.php'; // require funktioniert nicht ???
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

	if ($connection->connect_error) die($connection->connect_error);

	$topcatid = sanitizeMySQL($connection, $topcatid);

	$query = "SELECT * FROM categories WHERE ischildof = $topcatid";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	$rows = $result->num_rows;

	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		echo '<option value="' .$row['category_id'] . '">' . $row['name'] . '</option>';
	}
	$connection->close();
	return;
}

// get the categories of object defined by $oid
function getcategories($oid){
	include 'variables.php'; 
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$oid = sanitizeMySQL($connection, $oid);
	
	$query = "SELECT o.object_id AS oid, o.name AS oname, c.category_id AS catid, c.name AS catname, c.ischildof FROM objects o
		INNER JOIN objects_has_categories ohc ON o.object_id = ohc.object_id
		INNER JOIN categories c on ohc.category_id = c.category_id 
		WHERE o.object_id = $oid";
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
			$query2 = "SELECT * FROM categories WHERE category_id = $childof";
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

// return name of category $catid
function getcategoryname($catid){
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$catid = sanitizeMySQL($connection, $catid);
	$query = "SELECT * FROM categories WHERE category_id = $catid";
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

function getfees($uid) {
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT * FROM membershipfees WHERE user_id = '$uid'";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error in getfees" . $connection->error );
	
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

function getlendedobjects($uid) {
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT * FROM objects WHERE owner = '$uid'";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error in getlendedobjects" . $connection->error );
	
	$rows = $result->num_rows;
	$objectslist[] = NULL;
	
	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$objectslist[$r]['name'] = $row['name'];
		$objectslist[$r]['until'] = $row['loaneduntil'];
		$objectslist[$r]['oid'] = $row['object_id'];
	}
	$connection->close();
	return $objectslist;
}

function getrentalsbyobject($oid) {
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT loanedout, duedate, givenback, r.comment, firstname, lastname, u.user_id AS userid 
		FROM rented r INNER JOIN users u ON r.user_id = u.user_id WHERE r.object_id = '$oid' ORDER BY loanedout ASC";
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

function getrentalsbyuser($uid) {
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT loanedout, duedate, givenback, r.comment, name AS objectname, o.object_id AS objectid 
		FROM rented r INNER JOIN objects o ON r.object_id = o.object_id WHERE r.user_id = '$uid' ORDER BY loanedout ASC";
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

function isvaliduser($uid) {
	// -3 = is locked, -2 = did not lend object, -1 = wrong usertype, 0 = invalid, 1 = valid for less than 6 weeks, 
	// 2 = valid longer than 6 weeks, 3 = did lend object, 4 = user is admin
	
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	$query = "SELECT usertype, islocked FROM users WHERE user_id = '$uid'";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error " . $connection->error );
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	// if user is only object owner -> invalid
	if ($row['usertype'] > 2) return -1;
	// if user is admin -> valid without fees
	if ($row['usertype'] == 1) return 4;
	if ($row['islocked'] == 1) return -3;
	
	
	if ($usermustlend == 0) { 
		$valid = 0;
		$fees = getfees($uid);
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
		// if no valid fee is found return $valid with its default 0
		return $valid;
	}
	if ($usermustlend == 1) {
		$valid = -2;
		$objects = getlendedobjects($uid);
		$now = date_create('now');
		foreach ($objects as $object) {
			if ($object['name'] == NULL) return -2;
			$until = date_create($object['until']);
			$toend = date_diff($now, $until);
			if ($toend->format('%R%a') >= 0) return 3;
		}
		return $valid;
	}
}

// return -1 - wrong status, 0 rented away, 1 available
function objectisavailable($oid) {
	$available = 0;
	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);

	// check wether object is marked broken or stolen
	$query = "SELECT isavailable FROM objects WHERE object_id = '$oid'";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database error " . $connection->error );
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	if ($row['isavailable'] > 1) return -1;
	
	// $query = "SELECT loanedout, duedate, givenback, isavailable FROM rented r INNER JOIN objects o ON r.objects_ID = o.ID WHERE o.ID = '$id'";
	$query = "SELECT givenback FROM rented WHERE object_id = '$oid'";
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


function paginate($total) {
	$limit = 10;
	$retval['footer'] = "";
	$retval['query'] = "";

	include 'variables.php';
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);

	$pages = ceil($total / $limit);
	$getpage = isset($_GET['page']) ? sanitizeMySQL($connection, $_GET['page']) : 1 ; 
	$page = min($pages, $getpage);
	$offset = ($page - 1)  * $limit;

	// prevent negative offset
	$offset = ($offset < 0) ? 0 : $offset;
	// if no data set start to 0
	$start = ($total == 0) ? 0: $offset + 1;
	
	
	$end = min(($offset + $limit), $total);

	// if query string is empty or only contains 'page=123' start with a ?
	if ($_SERVER['QUERY_STRING'] == '' || preg_match("/^page=[0-9]+$/", $_SERVER['QUERY_STRING'])) {
		 $myquery = "?page=" ;
	} else {
		// strip out &page or ?page out of the query string and append with &
		 $myquery= "?" . preg_replace("/(\?|&)page=[0-9]+/", "", $_SERVER['QUERY_STRING']) . "&page=";	 	
	}

	$prevlink = ($page > 1) ? '<a class="larrows" href="' . $myquery . '1" title="Erste Seite">&laquo;</a> &ensp; <a class="larrows" href="' . $myquery . ($page - 1) . '" title="Vorige Seite">&lsaquo;</a>' : '<span class="disabled larrows">&laquo;</span> &ensp; <span class="disabled larrows">&lsaquo;</span>';
	$nextlink = ($page < $pages) ? ' <a class="rarrows" href="' . $myquery . $pages . '" title="Letzte Seite">&raquo;</a>  &ensp; <a class="rarrows" href="' . $myquery . ($page + 1) . '" title="N&auml;chste Seite">&rsaquo;</a>' : '<span class="disabled rarrows">&raquo;</span> &ensp; <span class="disabled rarrows">&rsaquo;</span>';

	$retval['footer'] = "<div id='paging'><p>" . $prevlink . $nextlink . " Seite " . $page . " von " . $pages . " Seiten <br> Datens&auml;tze " . $start . " bis " . $end . " von insgesamt " . $total . "</p></div>";
	$retval['query'] =" LIMIT $limit OFFSET $offset ";
	return $retval;
}

?>