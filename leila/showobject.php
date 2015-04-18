<?php
require_once 'variables.php';
require_once 'tools.php';

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

if ($connection->connect_error) die($connection->connect_error);

$query = "SELECT * FROM objects WHERE ID = " . $_GET['ID'];
$result = $connection->query($query);

if (!$result) die ("Database query error" . $connection->error);

$result->data_seek(0);
$row = $result->fetch_array(MYSQLI_ASSOC);


?>

<html>
<body>
<h1>Objekt anzeigen</h1>
<img src="showimage.php?ID=<?=$row['ID']?>&showthumb"><br>
Objekt ID <input disabled="disabled" type="text" value="<?= $row['ID']?>"> <br>
<?php 
foreach (getcategories($_GET['ID']) as $cat){
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

</body>
</html>