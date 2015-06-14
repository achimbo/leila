<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if ($allowguests == 0 && (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin")) die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);
if (isset($_GET['ID']) ){
	$oid = sanitizeMySQL($connection, $_GET['ID']);
} else {
	die("missing query");
}

$query = "SELECT * FROM objects WHERE object_id = " . $oid;
$result = $connection->query($query);

if (!$result) die ("Database query error" . $connection->error);

$result->data_seek(0);
$row = $result->fetch_array(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html>
<head>
   <link rel="stylesheet" href="leila.css" type="text/css">
</head>
<body>
<?php
if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == "admin") include 'menu.php';
?>
<div id="content">
<h1><?= $row['name']?></h1>
<a href="showimage.php?ID=<?=$row['object_id']?>"><img src="showimage.php?ID=<?=$row['object_id']?>&showthumb"></a><br>
Objekt ID <?= $row['object_id']?> <p>
<?php 
foreach (getcategories($oid) as $cat){
	echo 'Kategorie <a href="listobjects.php?catid=' . $cat['catid'] . '">' . $cat['name'] . '</a><br>';
}
?><p>
Beschreibung: <?= $row['description']?><br>
Hinzugef&uuml;gt am <?= $row['dateadded']?> <br>
<br>
<?php 
if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == "admin") {
	echo <<<_END
	<a href="editobject.php?ID=$oid"><b>Objekt Editieren</b></a><p>
	<a href="lendobject.php?objectid=$oid"><b>Objekt verleihen</b></a><p>
_END;

	$rentals = getrentalsbyobject($oid);
	echo "<table id='rentallist'>";
	switch (objectisavailable($oid)) {
		case -1:
			echo "<caption><div class='invalid'>Falscher Status</div></caption>";
			break;
	
		case 0:
			echo "<caption><div class='invalid'>Objekt verliehen</div></caption>";
			break;
	
		case 1:
			echo "<caption><div class='valid'>Objekt verleihbar</div></caption>";
			break;
	}
	echo "<thead><tr><th>Username</th><th>Von</th><th>Bis</th><th>Zur&uuml;ck</th><th>Kommentar</th></thead>";
	
	foreach ($rentals as $rent) {
		echo "<tr><td><a href='editmember.php?ID=" . $rent['userid'] . "'>" . $rent['firstname'] . " " . $rent['lastname'] . "</a></td>";
		echo "<td><a href='lendobject.php?edit=1&userid=" . $rent['userid'] . "&objectid=" . $oid . "&loanedout=" . $rent['loanedout'] . "'>". $rent['loanedout'] . "</a></td>" ;
		echo "<td>" . $rent['duedate'] . "</td><td>" . $rent['givenback'] . "</td><td>" . $rent['comment'] . "</td></tr>";
	}
	echo "</table>";
} else {
	switch (objectisavailable($oid)) {
		case -1:
			echo "<span class='invalid'>Falscher Status</span>";
			break;
	
		case 0:
			echo "<span class='invalid'>Objekt verliehen</span>";
			break;
	
		case 1:
			echo "<span class='valid'>Objekt verleihbar</span>";
			break;
	}
}
			
?>
</div>
</body>
</html>