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
	$query = "SELECT COUNT(*) AS count FROM leila.users WHERE CONCAT(firstname, ' ', lastname) LIKE '%$searchstring%' ORDER BY lastname";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	$message = "mit Namen " . $searchstring;

	$query = "SELECT * FROM leila.users WHERE CONCAT(firstname, ' ', lastname) LIKE '%$searchstring%' ORDER BY lastname" . $pag['query'];
	
} elseif (isset($_GET['showadmins'])) {
	$query = "SELECT COUNT(*) AS count FROM leila.users WHERE usertype = 1 ORDER BY lastname";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	$message = "die Admin sind";
	
	$query = "SELECT * FROM leila.users WHERE usertype = 1 ORDER BY lastname" . $pag['query'];
	
} elseif (isset($_GET['searchid'])) {
	$searchid = sanitizeMySQL($connection, $_GET['searchid']);
	$query = "SELECT * FROM users WHERE user_id = '$searchid'";
	$message = "mit ID " . $searchid;
	$pag['footer'] = "";
} else {
	$query = "SELECT COUNT(*) AS count FROM users;";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	
	$query = "SELECT * FROM users ORDER BY lastname" . $pag['query'];
}

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table class='memberlist'>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$mylist .= "<tr><td><a href='editmember.php?ID=" . $row['user_id'] . "'>" . $row['firstname'] . " " . $row['lastname'] . "</a></td></tr>\n";
	
	// $mylist .= "<tr><td> Name <a href='showmember.php?ID=' .$row['user_id'] > $row['firstname'] . </a></td></tr> ";
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

<h1>Mitglieder &Uuml;bersicht</h1>
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
<form method="get" action="listmembers.php">
	<input type="submit" name="showadmins" value="Admins anzeigen">
</form>	

<h3>Mitglieder <?= $message?></h3>
<?= $mylist?>
<?= $pag['footer']?>
</div>

</body>
</html>