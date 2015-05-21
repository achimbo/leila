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


?>

<!DOCTYPE html>
<html>
<head>
   <link rel="stylesheet" href="leila.css" type="text/css">
</head>
<body>
<?php include 'menu.php';?>
<div id="content">
<h1>Objekt anzeigen</h1>
<a href="showimage.php?ID=<?=$row['ID']?>"><img src="showimage.php?ID=<?=$row['ID']?>&showthumb"></a><br>
Objekt ID <input disabled="disabled" type="text" value="<?= $row['ID']?>"> <br>
<?php 
foreach (getcategories($id) as $cat){
	echo 'Kategorie <a href="listobjects.php?catid=' . $cat['catid'] . '">' . $cat['name'] . '</a><br>';
}
?>
Objekt Name <input disabled="disabled" type="text" value="<?= $row['name']?>"> <br>
Objekt Beschreibung <textarea disabled="disabled"><?= $row['description']?></textarea> <br>
Datum hinzugef&uuml;gt <input disabled="disabled" type="text" value="<?= $row['dateadded']?>"> <br>
Interner Kommentar <textarea disabled="disabled"><?= $row['internalcomment']?></textarea> <br>
Eigent&uuml;er ID <input disabled="disabled" type="text" value="<?= $row['owner']?>"> <br>
Geliehen bis <input disabled="disabled" type="text" value="<?= $row['loaneduntil']?>"> <br>
Ist verf&uuml;gbar <input disabled="disabled" type="text" value="<?= $row['isavailable']?>"> <br>
<br>
<a href="editobject.php?ID=<?=$id?>"><b>Objekt Editieren</b></a><p>
<a href="lendobject.php?objectid=<?=$id?>"><b>Objekt verleihen</b></a><p>
<?php 

$rentals = getrentalsbyobject($id);
echo "<table id='rentallist'>";
switch (objectisavailable($id)) {
	case -1:
		echo "<caption><div class='invalid'>Falscher Status</span></caption>";
		break;

	case 0:
		echo "<caption><div class='invalid'>Objekt verliehen</span></caption>";
		break;

	case 1:
		echo "<caption><div class='valid'>Objekt verleihbar</span></caption>";
		break;
}
echo "<thead><tr><th>Username</th><th>Von</th><th>Bis</th><th>Zur&uuml;ck</th><th>Kommentar</th></thead>";

foreach ($rentals as $rent) {
	echo "<tr><td><a href='editmember.php?ID=" . $rent['userid'] . "'>" . $rent['firstname'] . " " . $rent['lastname'] . "</a></td>";
	echo "<td>" . $rent['loanedout'] . "</td><td>" . $rent['duedate'] . "</td><td>" . $rent['givenback'] . "</td><td>" . $rent['comment'] . "</td></tr>";
}
echo "</table>"
?>
</div>
</body>
</html>