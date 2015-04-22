<?php
require_once 'variables.php';
require_once 'tools.php';

if (isset($_POST['name']) && !isset($_POST['getsubcategories'])) {
	$error = checkname($_POST['name']);
	$error .= mycheckdate($_POST['dateadded']);
	$error .= mycheckdate($_POST['loaneduntil']);
}

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);
if (isset($_GET['ID']) ){
	$id = sanitizeMySQL($connection, $_GET['ID']);
} else {
	die("missing object ID");
}


if (isset($_POST['addcategory'])) {
	if (!isset($_POST['subcategory'])) {
		$topcat = sanitizeMySQL($connection, $_POST['topcategory']);
		$query = "INSERT INTO objects_has_categories (objects_ID, categories_ID) VALUES ('$id', '$topcat') ";
	} else {
		$subcat = sanitizeMySQL($connection, $_POST['subcategory']);
		$query = "INSERT INTO objects_has_categories (objects_ID, categories_ID) VALUES ('$id', '$subcat') ";
	}
	$result = $connection->query($query);
	if (!$result) die ("Kategorie kann nicht doppelt vergeben werden " . $connection->error);
}

if (isset($_POST['deleteimage'])) {
	$query = "UPDATE objects SET image = NULL, imagename = NULL, imagetype = NULL, scaledimage = NULL WHERE ID = $id";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
}

if (isset($_POST['deleteobject'])) {
	$query = "DELETE FROM objects WHERE ID = $id";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	include "menu.php";
	die ("Objekt gel&ouml;scht <br> Zur <a href=\"listobjects.php\">&Uuml;bersicht</a>");
}


if (isset($_POST['saveobject']) && $error == "") {
	$name = sanitizeMySQL($connection, $_POST['name']);
	$description = sanitizeMySQL($connection, $_POST['description']);
	$dateadded = sanitizeMySQL($connection, $_POST['dateadded']);
	$internalcomment = sanitizeMySQL($connection, $_POST['internalcomment']);
	$owner = sanitizeMySQL($connection, $_POST['owner']);
	$loaneduntil = sanitizeMySQL($connection, $_POST['loaneduntil']);
	$isavailable = sanitizeMySQL($connection, $_POST['isavailable']);
	
	// set NULL or mysql complains
	$owner != '' ? $owner = addquotes($owner) : $owner = 'NULL';
	$loaneduntil != '' ? $loaneduntil = addquotes($loaneduntil) : $loaneduntil = 'NULL';
	$dateadded != '' ? $dateadded = addquotes($dateadded) : $dateadded = 'NULL';
	$description != '' ? $description = addquotes($description) : $description = 'NULL';
	$internalcomment != '' ? $internalcomment = addquotes($internalcomment) : $internalcomment = 'NULL';
	
	//	print_R($_FILES['image']);
	if (file_exists($_FILES['image']['tmp_name'])){
		$imagename = sanitizeMySQL($connection, $_FILES['image']['name']);
		$imagetype = sanitizeMySQL($connection, $_FILES['image']['type']);
		$tmpname  = $_FILES['image']['tmp_name'];
	
		$image = file_get_contents($tmpname);
		$image = $connection->real_escape_string($image);
	
		$img = new imagick($_FILES['image']['tmp_name']);
		$img->scaleImage(100, 75);
		$imagescaled = $img->getimageblob();
		$imagescaled = $connection->real_escape_string($imagescaled);
	
		$imagename = addquotes($imagename);
		$imagetype = addquotes($imagetype);
		$image = addquotes($image);
		$imagescaled = addquotes($imagescaled);

		$query = "UPDATE objects SET name = '$name', description = $description, image = $image, imagename = $imagename,
		imagetype = $imagetype, scaledimage = $imagescaled, dateadded = $dateadded, internalcomment = $internalcomment,
		owner = $owner, loaneduntil = $loaneduntil, isavailable = $isavailable WHERE ID = $id";
	} else {
		// rewrite to NULL / addquotes() ?
		$query = "UPDATE objects SET name = '$name', description = $description, dateadded = $dateadded, 
		internalcomment = $internalcomment, owner = $owner, loaneduntil = $loaneduntil, isavailable = $isavailable WHERE ID = $id";
	}
	
	//	echo "Query ist " . $query;
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	
	if (isset($_POST['deletecat'])){
		foreach($_POST['deletecat'] as $delcat) {
			$delcat = sanitizeMySQL($connection, $delcat);
			$query = "DELETE FROM objects_has_categories WHERE objects_id = $id AND categories_id = $delcat";
			$result = $connection->query($query);
			if (!$result) die ("Database query error" . $connection->error);
		}
	}
}


$query = "SELECT * FROM objects WHERE ID = " . $id;
$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);

$result->data_seek(0);
$row = $result->fetch_array(MYSQLI_ASSOC);

?>


<html>
<body>
<?php include 'menu.php';
if (isset($error) && $error != "") echo "<div class='errorclass'>Fehler: $error";
?>

<h1>Objekt bearbeiten</h1>
<form method="post" action="editobject.php?ID=<?=$row['ID']?>"  enctype="multipart/form-data">
	<!-- hidden submit, so that enter button in name field works, else "getsubcategories" would be default -->
	<input type="submit" name="saveobject" value="hs" style="visibility: hidden;" /><br>
	Objekt ID <input disabled="disabled" name="id" type="text" value="<?= $row['ID']?>"> <br>
	Kategorien <br>
	<?php 
	foreach (getcategories($id) as $cat){
		echo $cat['name'] . "<input type=\"checkbox\" name=\"deletecat[]\" value=\"" . $cat['catid'] . "\"> l&ouml;schen<br>";
		//echo "Kategorie" . $cat['name'] . $cat['catid'];
	}
	?>
	
	Kategorie hinzuf&uuml;gen 	<select name="topcategory" size="1">
		<?php gettopcategories(); ?>
	</select>
	<?php 
	if (isset($_POST['getsubcategories'])){
		echo '<select name ="subcategory" size="1">';
		getsubcategories($_POST['topcategory']);
		echo '</select>';
	} 
	?> 
	<input type="submit" name="getsubcategories" value="Sub Kat anzeigen">
	<input type="submit" name="addcategory" value="Kat hinzuf&uuml;gen"> <br>
	
	<?= isset($_POST['name']) && ($_POST['name'] == '') ? '<div class="errorclass">Name eingeben</div>' : '' ?> 
	Objekt Name <input type="text" name="name" value="<?= $row['name']?>"> <br>
	Objekt Beschreibung <textarea name ="description" rows="5" cols="20"><?= $row['description']?></textarea> <br>
	<a href="showimage.php?ID=<?=$row['ID']?>"><img src="showimage.php?ID=<?=$row['ID']?>&showthumb"></a>
	<?php if ($row['image'] != NULL) {echo "<input type=\"submit\" name=\"deleteimage\" value=\"Bild l&ouml;schen\"><br>" ;}?>
	Foto &auml;ndern<input type="file" name="image"> <br>
	Datum hinzugef&uuml;gt <input type="text" name="dateadded" value="<?= $row['dateadded']?>"> <br>
	Interner Kommentar <textarea name ="internalcomment" rows="5" cols="20"><?= $row['internalcomment']?></textarea> <br>
	Eigent&uuml;er ID <input type="text" name="owner" value="<?= $row['owner']?>"> <br>
	Geliehen bis <input type="text" name="loaneduntil" value="<?= $row['loaneduntil']?>"> <br>
	Status 
	<select name="isavailable" size="1">
		<option value="1" <?php if ($row['isavailable'] == 1) {echo "selected=\"selected\" ";}?> >Ist da</option>
		<option value="2" <?php if ($row['isavailable'] == 2) {echo "selected=\"selected\" ";}?> >Ist kaputt </option>
		<option value="3" <?php if ($row['isavailable'] == 3) {echo "selected=\"selected\" ";}?> >Ist verschwunden </option>
	</select>
	<br>
	<input type="submit" name="saveobject" value="&Auml;nderungen speichern"><br>
	<input type="submit" name="deleteobject" value="Objekt l&ouml;schen">
</form>
</body>
</html>