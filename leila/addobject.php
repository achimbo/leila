<?php
require_once 'variables.php';
require_once 'tools.php';

if (isset($_POST['name']) && !isset($_POST['getsubcategories'])) {
	$error = checkname($_POST['name']);
	$error .= mycheckdate($_POST['dateadded']);
	$error .= mycheckdate($_POST['loaneduntil']);
}


if (!isset($_POST['getsubcategories']) && isset($_POST['name']) && $error == ""){
	
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	
	$name = sanitizeMySQL($connection, $_POST['name']);
	$description = sanitizeMySQL($connection, $_POST['description']);
	$dateadded = sanitizeMySQL($connection, $_POST['dateadded']);
	$internalcomment = sanitizeMySQL($connection, $_POST['internalcomment']);
	$owner = sanitizeMySQL($connection, $_POST['owner']);
	$loaneduntil = sanitizeMySQL($connection, $_POST['loaneduntil']);

//	print_R($_FILES['image']);
	if (file_exists($_FILES['image']['tmp_name'])){
		$imagename = sanitizeMySQL($connection, $_FILES['image']['name']);
		$imagetype = sanitizeMySQL($connection, $_FILES['image']['type']);
		$tmpname  = $_FILES['image']['tmp_name'];
		
		switch ($imagetype) {
			case 'image/jpeg': break;
			case 'image/png': break;
			case 'image/gif': break;
			default: die("Error, only jpg, png or gif images allowed!");
		}
		
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
	
	} else {
		// rewrite to NULL / addquotes() ?
		$imagename = 'NULL';
		$imagetype = 'NULL';
		$image = 'NULL';
		$imagescaled = 'NULL';
	}


    
	// set NULL or mysql complains
	$owner != '' ? $owner = addquotes($owner) : $owner = 'NULL';
	$loaneduntil != '' ? $loaneduntil = addquotes($loaneduntil) : $loaneduntil = 'NULL';
	$dateadded != '' ? $dateadded = addquotes($dateadded) : $dateadded = 'NULL';
	$description != '' ? $description = addquotes($description) : $description = 'NULL';
	$internalcomment != '' ? $internalcomment = addquotes($internalcomment) : $internalcomment = 'NULL';
	
	
	$query = "INSERT INTO objects (name, description, image, imagename, imagetype, scaledimage, dateadded, internalcomment, owner, loaneduntil, isavailable) 
		VALUES ('$name', $description, $image, $imagename, $imagetype, $imagescaled, $dateadded, $internalcomment, $owner, $loaneduntil, '1')" ;
//	echo "Query ist " . $query;
	$result = $connection->query($query);
	if (!$result) { 
		die ("Angaben fehlerhaft, Objekt nicht erstellt " . $connection->error);
		$message = '<div class="errorclass">Fehler, Objekt nicht erstellt</div>';
	} else {
		if (isset($_POST['subcategory'])) {$cat = sanitizeMySQL($connection, $_POST['subcategory']); } 
			else {$cat = sanitizeMySQL($connection, $_POST['topcategory']);	}
			$insid = mysqli_insert_id($connection);
		$query = "INSERT INTO objects_has_categories (objects_ID, categories_ID) 
				VALUES ('$insid', '$cat' )";
//		echo "Query ist " . $query;
		$result = $connection->query($query);
		if (!$result) {
			die ("Angaben fehlerhaft, Kategorie nicht erstellt " . $connection->error);
			$message = '<div class="errorclass">Fehler, Kategorie nicht erstellt</div>';
		} 
			else {$message = '<div class="message"><a href="editobject.php?ID=' .$insid . '"> Objekt</a> erstellt</div>';}
	}
}
?>


<html>
<head>
<title>Objekt hinzuf&uuml;gen</title>
</head>
<body>
<?php include 'menu.php';
if (isset($error) && $error != "") echo "<div class='errorclass'>Fehler: $error";
?>
<?= isset($message) ? $message : ''?>
<h1>Objekt hinzuf&uuml;gen</h1>
<form method="post" action="addobject.php"  enctype="multipart/form-data">
<!-- hidden submit, so that enter button in name field works, else "getsubcategories" would be default -->
<input type="submit" value="hs" style="visibility: hidden;" /><br>
<?= isset($_POST['name']) && ($_POST['name'] == '') ? '<div class="errorclass">Name eingeben</div>' : '' ?> 
Name <input type="text" name="name" Name ="name" value="<?php 
	if(isset($_POST['name']) && isset($_POST['getsubcategories'])){ echo $_POST['name']; } ?>" >  <br>

Kategorie 	<select name="topcategory" size="1">
		<?php gettopcategories(); ?>
	</select>
	<?php 
	if (isset($_POST['getsubcategories'])){
		echo '<select name ="subcategory" size="1">';
		getsubcategories($_POST['topcategory']);
		echo '</select>';
	} 
	?> 
	<input type="submit" name="getsubcategories" value="Sub Kat anzeigen"> <br>
Beschreibung <textarea name ="description" rows="5" cols="20"><?php if(isset($_POST['description'])){ echo $_POST['description']; } ?></textarea> <br>
Foto <input type="file" name="image"> <br>
Eingangsdatum JJJJ-MM-DD <input type="text" name="dateadded" value="<?= getcurrentdate()?>"> <br>
Interner Kommentar <textarea name ="internalcomment" rows="5" cols="20"><?php if(isset($_POST['internalcomment'])){ echo $_POST['internalcomment']; } ?>
</textarea> <br>
Eigent&uuml;mer ID <input type="text" name="owner" value="<?php if(isset($_POST['owner'])){ echo $_POST['owner']; } ?>"> <br>
Geliehen bis JJJJ-MM-DD <input type="text" name="loaneduntil" value="<?php if(isset($_POST['loaneduntil'])){ echo $_POST['loaneduntil']; } ?>"> <br>
<input type="submit" name="addobject" value="Objekt anlegen">
</form>

</body>
</html>
