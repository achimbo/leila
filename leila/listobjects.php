<?php
require_once 'variables.php';
require_once 'tools.php';

$mylist = '';
$message = '';

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);

if (isset($_GET['catid']) ){
	$catid = sanitizeMySQL($connection, $_GET['catid']);
};

if (isset($_GET['searchstring']) ){
	$searchstring = sanitizeMySQL($connection, $_GET['searchstring']);
};

if (isset($_GET['searchid']) ){
	$searchid = sanitizeMySQL($connection, $_GET['searchid']);
};

if (isset($catid) ){
	$query = "SELECT o.* FROM objects o
	INNER JOIN objects_has_categories ohc ON o.ID = ohc.objects_ID		
    INNER JOIN categories c on ohc.categories_ID = c.ID 
	WHERE ohc.categories_ID = $catid OR c.ischildof = $catid";	
	$message = "in Kategorie " . getcategoryname($catid);
} elseif (isset($searchstring)){
	$query = "SELECT * FROM objects WHERE MATCH(name, description) AGAINST ('$searchstring' IN BOOLEAN MODE)";
	$message = "mit Inhalt " . $searchstring;
} elseif (isset($searchid)) {
	$query = "SELECT * FROM objects WHERE ID = '$searchid'";
	$message = "mit ID " . $searchid;	
} else {
	$query = "SELECT * FROM objects";
}


 echo $query;
$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table class='objectlist'>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	
	$mylist .= '<tr><td> Name <a href="showobject.php?ID=' .$row['ID'] . '">' . $row['name'] . '</a>
			<img src="showimage.php?ID=' . $row['ID'] . '&amp;showthumb" alt="Objekt Bild"></td></tr> ';
	//$mylist .= 'Description ' . $row['description'] . '<br>';
}

$mylist .= "</table>";

?>

<!DOCTYPE html>
<html>
<head>
	<title>List Objects</title>
   <link rel="stylesheet" href="leila.css" type="text/css">
</head>
<body>

<?php include 'menu.php';?>
<div id="content">
<h3>Objekte suchen</h3>
<?php echo "<div id='cats'>";
	 getcategoriesaslinks();
	 echo "</div>";
?>
<form method="get" action="listobjects.php">
	<label for="searchstring">In Beschreibung und Titel suchen: </label>
	<input type="text" id="searchstring" name="searchstring">
	<input type="submit" value="Suchen">
</form>
<form method="get" action="listobjects.php">
	<label for="searchid">In ID suchen: </label>
	<input type="text" id="searchid" name="searchid">
	<input type="submit" value="ID suchen">
</form>
<h3>Objekte <?= $message?></h3>
<?= $mylist?>
</div>
</body>
</html>