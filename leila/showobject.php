<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

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
<?php include 'menu.php';?>
<div id="content">
<h1>Objekt anzeigen</h1>
<a class="floatright" href="showimage.php?ID=<?=$row['object_id']?>"><img src="showimage.php?ID=<?=$row['object_id']?>&showthumb"></a><br>
<label for="id">Objekt ID</label> <input class="nowidth" id="id" disabled="disabled" type="text" value="<?= $row['object_id']?>"> <p>
<?php 
foreach (getcategories($oid) as $cat){
	echo 'Kategorie <a href="listobjects.php?catid=' . $cat['catid'] . '">' . $cat['name'] . '</a><br>';
}
?><p>
<label for="name">Objekt Name</label> <input id="name" disabled="disabled" type="text" value="<?= $row['name']?>"> <br>
<label for="description">Objekt Beschreibung</label> <textarea id="description" disabled="disabled"><?= $row['description']?></textarea> <br>
<label for="dateadded">Hinzugef&uuml;gt am</label> <input id="dateadded" disabled="disabled" type="text" value="<?= $row['dateadded']?>"> <br>
<label for="internalcomment">Interner Kommentar</label> <textarea id="internalcomment" disabled="disabled"><?= $row['internalcomment']?></textarea> <br>
<label for="owner">Eigent&uuml;mer ID</label> <input id="owner" disabled="disabled" type="text" value="<?= $row['owner']?>"> <br>
<label for="loaneduntil">Geliehen bis </label><input id="loaneduntil" disabled="disabled" type="text" value="<?= $row['loaneduntil']?>"> <br>
<label for="isavailable">Ist verf&uuml;gbar </label><input id="isavailable" disabled="disabled" type="text" value="<?= $row['isavailable']?>"> <br>
<br>
<a href="editobject.php?ID=<?=$oid?>"><b>Objekt Editieren</b></a><p>
<a href="lendobject.php?objectid=<?=$oid?>"><b>Objekt verleihen</b></a><p>
<?php 

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
echo "</table>"
?>
</div>
</body>
</html>