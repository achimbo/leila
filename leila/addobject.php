<?php
require_once 'variables.php';
require_once 'tools.php';

if (!isset($_POST['getsubcategories']) && (isset($_POST['name']))){
	echo 'objekt anlegen';
	
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	
	$name = sanitizeMySQL($connection, $_POST['name']);
	$description = sanitizeMySQL($connection, $_POST['description']);
	$dateadded = sanitizeMySQL($connection, $_POST['dateadded']);
	$internalcomment = sanitizeMySQL($connection, $_POST['internalcomment']);
	$owner = sanitizeMySQL($connection, $_POST['owner']);
	$loaneduntil = sanitizeMySQL($connection, $_POST['loaneduntil']);

	// set values or mysql complains
	if ($owner == '') $owner = 1;
	if ($loaneduntil == '') $loaneduntil = "2050-1-1";
	
	$query = "INSERT INTO objects (name, description, dateadded, internalcomment, owner, loaneduntil, isavailable) 
		VALUES ('$name', '$description', '$dateadded', '$internalcomment', '$owner', '$loaneduntil', '1')" ;
	$result = $connection->query($query);
	if (!$result) die ("Angaben fehlerhaft, Objekt nicht erstellt " . $connection->error);
}
?>


<html>
<head>
<title>Objekt hinzuf&uuml;gen</title>
</head>
<body>

<h1>Objekt hinzuf&uuml;gen</h1>
<form method="post" action="addobject.php">
<input type="submit" value="hiddensubmit" style="visibility: hidden;" />
<?= isset($_POST['addobject']) && ($_POST['name'] == '') ? '<div class="errorclass">Name eingeben</div>' : '' ?> 
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
Beschreibung <textarea name ="description" rows="5" cols="20"><?php if(isset($_POST['internalcomment']) && isset($_POST['internalcomment'])){ echo $_POST['description']; } ?></textarea> <br>
Foto <input type="file" name="photo"> <br>
Eingangsdatum JJJJ-MM-DD <input type="text" name="dateadded" value="<?= getcurrentdate()?>"> <br>
Interner Kommentar <textarea name ="internalcomment" rows="5" cols="20"><?php if(isset($_POST['internalcomment']) && isset($_POST['internalcomment'])){ echo $_POST['internalcomment']; } ?>
</textarea> <br>
Eigent&uuml;mer ID <input type="text" name="owner" value="<?php 
	if(isset($_POST['owner']) && isset($_POST['owner'])){ echo $_POST['owner']; } ?>"> <br>
Geliehen bis JJJJ-MM-DD <input type="text" name="loaneduntil" value="<?php 
	if(isset($_POST['loaneduntil']) && isset($_POST['loaneduntil'])){ echo $_POST['loaneduntil']; } ?>"> <br>
<input type="submit" name="addobject" value="Objekt anlegen">
</form>

</body>
</html>
