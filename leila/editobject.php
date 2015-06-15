<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

if (isset($_POST['name']) && !isset($_POST['getsubcategories'])) {
	$error = isempty($_POST['name'], "Name");
	$error .= mycheckdate($_POST['dateadded']);
	$error .= mycheckdate($_POST['loaneduntil']);
}

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);
if (isset($_GET['ID']) ){
	$oid = sanitizeMySQL($connection, $_GET['ID']);
} else {
	die("missing object ID");
}


if (isset($_POST['addcategory'])) {
	if (!isset($_POST['subcategory'])) {
		$topcat = sanitizeMySQL($connection, $_POST['topcategory']);
		$query = "INSERT INTO objects_has_categories (object_id, category_id) VALUES ('$oid', '$topcat') ";
	} else {
		$subcat = sanitizeMySQL($connection, $_POST['subcategory']);
		$query = "INSERT INTO objects_has_categories (object_id, category_id) VALUES ('$oid', '$subcat') ";
	}
	$result = $connection->query($query);
	if (!$result) die ("Kategorie kann nicht doppelt vergeben werden " . $connection->error);
}

if (isset($_POST['deleteimage'])) {
	$query = "UPDATE objects SET image = NULL, imagename = NULL, imagetype = NULL, scaledimage = NULL WHERE object_id = $oid";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
}

if (isset($_POST['deleteobject'])) {
	$query = "DELETE FROM objects WHERE object_id = $oid";
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	echo '<head> <link rel="stylesheet" href="leila.css" type="text/css"></head>';
	include "menu.php";
	die ("<div id='content'><h3>Objekt gel&ouml;scht </h3></div>");
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
	
		if ($imagelibrary == 'imagick') {
			$img = new imagick($_FILES['image']['tmp_name']);
			// pass 0 to scale automatically
			$img->scaleImage(0, 75);
			$imagescaled = $img->getimageblob();
			$imagescaled = $connection->real_escape_string($imagescaled);
		} elseif ($imagelibrary == 'gd')	{
					// Loading the image and getting the original dimensions
			$width = 100;
			switch ($imagetype) {
				case 'image/jpeg': 			
					$largeimage = imagecreatefromjpeg($_FILES['image']['tmp_name']);
					break;
				case 'image/png': 
					$largeimage = imagecreatefrompng($_FILES['image']['tmp_name']);
					break;
				case 'image/gif': 
					$largeimage = imagecreatefromgif($_FILES['image']['tmp_name']);
					break;
				default: die("Error, only jpg, png or gif images allowed!");
			}
			$orig_width = imagesx($largeimage);
			$orig_height = imagesy($largeimage);
			$height = (($orig_height * $width) / $orig_width);
			// Create new image to display
			$new_image = imagecreatetruecolor(100, 75);
			
			// Create new image with changed dimensions
			imagecopyresized($new_image, $largeimage,
					0, 0, 0, 0,
					$width, $height,
					$orig_width, $orig_height);
			
			// Print image
			
			ob_start();
			switch ($imagetype) {
				case 'image/jpeg':
				imagejpeg($new_image);
				break;
				case 'image/png':
				imagepng($new_image);
				break;
				case 'image/gif':
				imagegif($new_image);
				break;
				default: die("Error, only jpg, png or gif images allowed!");
			}
			$imagescaled = ob_get_clean();
			$imagescaled = $connection->real_escape_string($imagescaled);
		}
		
		$imagename = addquotes($imagename);
		$imagetype = addquotes($imagetype);
		$image = addquotes($image);
		$imagescaled = addquotes($imagescaled);

		$query = "UPDATE objects SET name = '$name', description = $description, image = $image, imagename = $imagename,
		imagetype = $imagetype, scaledimage = $imagescaled, dateadded = $dateadded, internalcomment = $internalcomment,
		owner = $owner, loaneduntil = $loaneduntil, isavailable = $isavailable WHERE object_id = $oid";
	} else {
		// rewrite to NULL / addquotes() ?
		$query = "UPDATE objects SET name = '$name', description = $description, dateadded = $dateadded, 
		internalcomment = $internalcomment, owner = $owner, loaneduntil = $loaneduntil, isavailable = $isavailable WHERE object_id = $oid";
	}
	
	//	echo "Query ist " . $query;
	$result = $connection->query($query);
	if (!$result) die ("Database query error" . $connection->error);
	
	if (isset($_POST['deletecat'])){
		foreach($_POST['deletecat'] as $delcat) {
			$delcat = sanitizeMySQL($connection, $delcat);
			$query = "DELETE FROM objects_has_categories WHERE object_id = $oid AND category_id = $delcat";
			$result = $connection->query($query);
			if (!$result) die ("Database query error" . $connection->error);
		}
	}
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
<div id='content'>
<?php if (isset($error) && $error != "") echo "<div class='errorclass'>Fehler: $error </div>";?>

<h1>Objekt bearbeiten</h1>
<form method="post" action="editobject.php?ID=<?=$row['object_id']?>" enctype="multipart/form-data">
	<!-- hidden submit, so that enter button in name field works, else "getsubcategories" would be default -->
	<input type="submit" name="saveobject" value="hs" style="visibility: hidden;" /><br>
<label for="id">Objekt ID</label> <input id="id" disabled="disabled" name="id" type="text" value="<?= $row['object_id']?>"> <br>
	<p>
	<b>Kategorien</b> <br>
	<?php 
	foreach (getcategories($oid) as $cat){
		echo $cat['name'] . "<input class='nowidth' type=\"checkbox\" name=\"deletecat[]\" value=\"" . $cat['catid'] . "\"> l&ouml;schen<br>";
		//echo "Kategorie" . $cat['name'] . $cat['catid'];
	}
	?>
	<br>
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
	<input type="submit" name="addcategory" value="Kat hinzuf&uuml;gen"> <p>
	
	<?= isset($_POST['name']) && ($_POST['name'] == '') ? '<div class="errorclass">Name eingeben</div>' : '' ?> 
	<label for="name">Objekt Name</label> <input id="name" type="text" name="name" value="<?= $row['name']?>"> <br>
	<label for="description">Objekt Beschreibung</label> <textarea id="description" name ="description" rows="5" cols="20"><?= $row['description']?></textarea> <p>
	<a href="showimage.php?ID=<?=$row['object_id']?>"><img src="showimage.php?ID=<?=$row['object_id']?>&showthumb"></a>
	<?php if ($row['image'] != NULL) {echo "<br><input type=\"submit\" name=\"deleteimage\" value=\"Bild l&ouml;schen\" onclick=\"return confirm('Sicher l&ouml;schen?');\"><br>" ;}?>
	Foto &auml;ndern<input type="file" name="image"> <p>
	<label for="dateadded">Hinzugef&uuml;gt am</label> <input id="dateadded" type="text" name="dateadded" value="<?= $row['dateadded']?>"> <br>
	<label for="internalcomment">Interner Kommentar</label> <textarea id="internalcomment" name ="internalcomment" rows="5" cols="20"><?= $row['internalcomment']?></textarea> <br>
	<label for="owner">Eigent&uuml;er ID</label> <input id="owner" type="text" name="owner" value="<?= $row['owner']?>"> <br>
	<label for="loaneduntil">Geliehen bis</label> <input id="loaneduntil" type="text" name="loaneduntil" value="<?= $row['loaneduntil']?>"> <br>
	<label for="isavailable">Status </label>
	<select id="isavailable" name="isavailable" size="1">
		<option value="1" <?php if ($row['isavailable'] == 1) {echo "selected=\"selected\" ";}?> >Ist da</option>
		<option value="2" <?php if ($row['isavailable'] == 2) {echo "selected=\"selected\" ";}?> >Ist kaputt </option>
		<option value="3" <?php if ($row['isavailable'] == 3) {echo "selected=\"selected\" ";}?> >Ist verschwunden </option>
	</select>
	<br>
	<input type="submit" name="saveobject" value="&Auml;nderungen speichern"><br>
	<input type="submit" name="deleteobject" value="Objekt l&ouml;schen" onclick="return confirm('Sicher l&ouml;schen?');">
</form>
</div>
</body>
</html>