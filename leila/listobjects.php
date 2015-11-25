<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if ($allowguests == 0 && (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin")) die ("Bitte <a href='login.php'>anmelden</a>");


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
	$query = "SELECT COUNT(*) AS count FROM objects o
	INNER JOIN objects_has_categories ohc ON o.object_id = ohc.object_id		
    INNER JOIN categories c on ohc.category_id = c.category_id 
	WHERE ohc.category_id = $catid OR c.ischildof = $catid ORDER BY o.name";	
	$message = "in Kategorie " . getcategoryname($catid);
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	
	$query = "SELECT o.* FROM objects o
	INNER JOIN objects_has_categories ohc ON o.object_id = ohc.object_ID
	INNER JOIN categories c on ohc.category_id = c.category_id
	WHERE ohc.category_id = $catid OR c.ischildof = $catid ORDER BY o.name" . $pag['query'];	
} elseif (isset($searchstring)){
	$query = "SELECT COUNT(*) AS count FROM objects WHERE (name LIKE '%$searchstring%') OR (description LIKE '%$searchstring%') ORDER BY name";
	$message = "mit Inhalt " . $searchstring;
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);	
	
	$query = "SELECT * FROM objects WHERE (name LIKE '%$searchstring%') OR (description LIKE '%$searchstring%') ORDER BY name" . $pag['query'];
} elseif (isset($searchid)) {
	$query = "SELECT * FROM objects WHERE object_id = '$searchid'";
	$pag['footer'] = "";
	$message = "mit ID " . $searchid;
} else {
	$query = "SELECT COUNT(*) AS count FROM objects;";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	
	$query = "SELECT * FROM objects ORDER BY name" . $pag['query'];
}


// echo $query;
$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table class='objectlist'>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	if (objectisavailable($row['object_id']) < 1 ) {
		$class = "class=unavailable";
	} else {
		$class = "class=available";
	}
	
	$mylist .= '<tr><td> Name <a ' . $class . ' href="showobject.php?ID=' .$row['object_id'] . '">' . $row['name'] . '
			<img src="showimage.php?ID=' . $row['object_id'] . '&amp;showthumb" alt="Objekt Bild"></a></td></tr> ';
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

<?php 
if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == "admin") include 'menu.php';
?>
<div id="content">
<h1>Objekt &Uuml;bersicht</h1>
<h3>Kategorien durchsuchen</h3>
<?php echo "<div id='cats'>";
	echo "<a href='listobjects.php'>Alle </a>";
	 getcategoriesaslinks();
	 echo "</div>";
?>
<form method="get" action="listobjects.php">
	<label for="searchstring">Beschr &amp; Titel: </label>
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
<?= $pag['footer']?>
</div>
</body>
</html>