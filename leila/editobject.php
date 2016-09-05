<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
require_once('configlocale.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die (_("please <a href='login.php'>login</a>"));

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
	if (!$result) die (_("category can not be assigned twice") . $connection->error);
}

if (isset($_POST['deleteimage'])) {
	$query = "UPDATE objects SET image = NULL, imagename = NULL, imagetype = NULL, scaledimage = NULL WHERE object_id = $oid";
	$result = $connection->query($query);
	if (!$result) die (_("Database query error") . $connection->error);
}

if (isset($_POST['deleteobject'])) {
	$query = "DELETE FROM objects WHERE object_id = $oid";
	$result = $connection->query($query);
	if (!$result) die (_("Database query error") . $connection->error);
	echo '<head> <link rel="stylesheet" href="leila-new.css" type="text/css">
	<link rel="stylesheet" href="leila-new.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css" type="text/css">
	<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">
	<script src="jquery/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="jquery-ui/jquery-ui.min.js"></script>
	</head>
	<body>
	<div class="container">';

	include "nav.php";
	echo ("<h3>" . _("object deleted") . "</h3></div></body></html>");
	die();
}


if (isset($_POST['saveobject']) && $error == "") {
	$name = sanitizeMySQL($connection, $_POST['name']);
	$description = sanitizeMySQL($connection, $_POST['description']);
	$dateadded = sanitizeMySQL($connection, $_POST['dateadded']);
	$internalcomment = sanitizeMySQL($connection, $_POST['internalcomment']);
	$owner = sanitizeMySQL($connection, $_POST['owner']);
	$loaneduntil = sanitizeMySQL($connection, $_POST['loaneduntil']);
	$isavailable = sanitizeMySQL($connection, $_POST['isavailable']);
	$shelf = sanitizeMySQL($connection, $_POST['shelf']);


	// set NULL or mysql complains
	$owner != '' ? $owner = addquotes($owner) : $owner = 'NULL';
	$loaneduntil != '' ? $loaneduntil = addquotes($loaneduntil) : $loaneduntil = 'NULL';
	$dateadded != '' ? $dateadded = addquotes($dateadded) : $dateadded = 'NULL';
	$description != '' ? $description = addquotes($description) : $description = 'NULL';
	$internalcomment != '' ? $internalcomment = addquotes($internalcomment) : $internalcomment = 'NULL';
	$shelf != '' ? $shelf = addquotes($shelf) : $shelf = 'NULL';


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
				default: die(_("Error, only jpg, png or gif images allowed!"));
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
				default: die(_("Error, only jpg, png or gif images allowed!"));
			}
			$imagescaled = ob_get_clean();
			$imagescaled = $connection->real_escape_string($imagescaled);
		}

		$imagename = addquotes($imagename);
		$imagetype = addquotes($imagetype);
		$image = addquotes($image);
		$imagescaled = addquotes($imagescaled);

		$query = "UPDATE objects SET name = '$name', description = $description, shelf = $shelf, image = $image, imagename = $imagename,
		imagetype = $imagetype, scaledimage = $imagescaled, dateadded = $dateadded, internalcomment = $internalcomment,
		owner = $owner, loaneduntil = $loaneduntil, isavailable = $isavailable WHERE object_id = $oid";
	} else {
		// rewrite to NULL / addquotes() ?
		$query = "UPDATE objects SET name = '$name', description = $description, shelf = $shelf, dateadded = $dateadded, 
		internalcomment = $internalcomment, owner = $owner, loaneduntil = $loaneduntil, isavailable = $isavailable WHERE object_id = $oid";
	}

	//	echo "Query ist " . $query;
	$result = $connection->query($query);
	if (!$result) die (_("Database query error") . $connection->error);

	if (isset($_POST['deletecat'])){
		foreach($_POST['deletecat'] as $delcat) {
			$delcat = sanitizeMySQL($connection, $delcat);
			$query = "DELETE FROM objects_has_categories WHERE object_id = $oid AND category_id = $delcat";
			$result = $connection->query($query);
			if (!$result) die (_("Database query error") . $connection->error);
		}
	}
}


$query = "SELECT * FROM objects WHERE object_id = " . $oid;
$result = $connection->query($query);
if (!$result) die (_("Database query error") . $connection->error);

$result->data_seek(0);
$row = $result->fetch_array(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="leila-new.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css" type="text/css">
	<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">
	<script src="jquery/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="jquery-ui/jquery-ui.min.js"></script>



	<meta charset="utf-8"/>
	<title><?= _('edit object')?></title>
</head>

<body onload="updateNames()">

<div class='container'>
	<?php include 'nav.php';?>
	<script type="text/javascript">
		document.getElementById('objectstab').className = 'active';
	</script>

	<?php if (isset($error) && $error != "") echo "<div class='alert alert-danger'>" . _('error') . ":" . $error . " </div><p>";?>

	<h1><?= _('edit object')?></h1>
	<div class="row margin-top">
		<div class="col-md-6">
			<form method="post" action="editobject.php?ID=<?=$row['object_id']?>" enctype="multipart/form-data">
				<div class="form-group">
					<!-- hidden submit, so that enter button in name field works, else "getsubcategories" would be default -->
					<input type="submit" name="saveobject" value="hs" style="visibility: hidden;" /><br>
					<label for="id"><?= _('object ID')?></label>
					<input disabled="disabled" class="form-control" id="id" name="id" type="text" value="<?= $row['object_id']?>">
				</div>
				<div class="form-group">
					<?= isset($_POST['name']) && ($_POST['name'] == '') ? '<div class="errorclass">' . _('enter name') . '</div>' : '' ?>
					<label for="name"><?= _('object name')?></label>
					<input id="name" class="form-control" type="text" name="name" value="<?= $row['name']?>"> <br>
				</div>



				<label><?= _('categories')?></label> <br>
				<?php
				foreach (getcategories($oid) as $cat){
					echo "<b>" . $cat['name'] . " </b>" . "<input class='nowidth' type=\"checkbox\" name=\"deletecat[]\" value=\"" . $cat['catid'] . "\"> " . _("delete") . "<br>";
					//echo "Kategorie" . $cat['name'] . $cat['catid'];
				}
				?>
				<label class="margin-top"><?= _('add category')?></label>
				<div class="row">
					<div class="col-md-3">
						<select class="form-control" name="topcategory" size="1">
							<?php gettopcategories(); ?>
						</select>
					</div>
					<?php
					if (isset($_POST['getsubcategories'])){
						echo '<div class="col-md-3"> <select class="form-control" name ="subcategory" size="1">';
						getsubcategories($_POST['topcategory']);
						echo '</select></div>';
					}
					?>
					<div class="col-md-3">
						<input type="submit" class="btn btn-default" name="getsubcategories" value="Sub Kat anzeigen">
					</div>
					<div class="col-md-3">
						<input type="submit" class="btn btn-default" name="addcategory" value="Kat hinzuf&uuml;gen">
					</div>
				</div>

				<div class="form-group margin-top">
				<label for="description"><?= _('object description')?></label>
					<textarea id="description" class="form-control" name ="description" rows="5" cols="20"><?= $row['description']?></textarea>
					</div>

				<div class="form-group">
					<label for="shelf"><?= _('shelf')?></label>
					<input id="shelf" class="form-control" type="text" name="shelf" value="<?= $row['shelf']?>">
					</div>

					<a class="img-rounded pull-left margin-right" href="showimage.php?ID=<?=$row['object_id']?>"><img src="showimage.php?ID=<?=$row['object_id']?>&showthumb"></a>
					<?php if ($row['image'] != NULL) {echo "<br><input type=\"submit\" class='btn btn-default' name=\"deleteimage\" value=\"" . _('delete image') . "\" onclick=\"return confirm('" . _('Are you sure you want to delete?') . "');\"><br>" ;}?>
				<?= _('upload photo')?><input type="file" class="btn btn-default" name="image"> <p>

				<div class="form-group">
					<label for="dateadded"><?= _('date added')?></label>
					<input id="dateadded" class="form-control" type="text" name="dateadded" value="<?= $row['dateadded']?>">
					</div>
				<div class="form-group">
					<label for="internalcomment"><?= _('internal comment')?></label>
					<textarea id="internalcomment" class="form-control" name ="internalcomment" rows="5" cols="20"><?= $row['internalcomment']?></textarea>
					</div>
				<div class="form-group">
					<label for="owner"><?= _('owner ID')?> &#x1f50e;</label>
					<input id="owner" class="form-control" type="text" name="owner" oninput="displayUserName(this)" value="<?= $row['owner']?>">
					</div>
				<div class="form-group">
					<label for="username"><?= _('owner name')?> &#x1f50e;</label>
					<input type="text" class="form-control" name="username" id="username" oninput="searchUserName(this)">
					<div id="usersearchbox"></div>
					</div>
				<div class="form-group">
				<label for="loaneduntil"><?= _('loaned until')?> &#x1f4c5;</label>
					<input id="loaneduntil" class="form-control" type="text" name="loaneduntil" value="<?= $row['loaneduntil']?>">
					</div>
				<script type="text/javascript">
					$( "#loaneduntil" ).datepicker({
						dateFormat: "yy-mm-dd",
						firstDay: 1,
						changeYear: true
					});

					$( "#loaneduntil" ).datepicker( "setDate", document.getElementById('loaneduntil').value );

				</script>
				<div class="form-group">
				<label for="isavailable"><?= _('availability status')?> </label>
				<select id="isavailable" class="form-control" name="isavailable" size="1">
					<option value="1" <?php if ($row['isavailable'] == 1) {echo "selected=\"selected\" ";}?> ><?= _('is here')?></option>
					<option value="2" <?php if ($row['isavailable'] == 2) {echo "selected=\"selected\" ";}?> ><?= _('is broken')?> </option>
					<option value="3" <?php if ($row['isavailable'] == 3) {echo "selected=\"selected\" ";}?> ><?= _('has disappeared')?> </option>
				</select>
				</div>
				<div class="form-group">
				<input type="submit" class="btn btn-default" name="saveobject" value="&Auml;nderungen speichern">
					</div>
				<div class="form-group">
				<input type="submit" class="btn btn-warning" name="deleteobject" value="Objekt l&ouml;schen" onclick="return confirm('Sicher l&ouml;schen?');">
					</div>
			</form>
			<form method="post" action="printlabel.php?ID=<?=$row['object_id']?>">
				<input type="submit" class="btn btn-default" name="printlabel" value="Label drucken"><br>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">

	function updateNames() {
		displayUserName(document.getElementById('owner'))
	}

	function displayUserName(input) {
		var request = new ajaxRequest()

		request.open("GET", "leilaservice.php?userid=" + input.value, true)
		request.send(null)

		request.onreadystatechange = function()
		{
			if (this.readyState == 4)
			{
				if (this.status == 200)
				{
					if (this.responseText != null)
					{
						document.getElementById('username').value = unescapeHtml(this.responseText)
					}
					else alert("Ajax error: No data received")
				}
				else alert( "Ajax error: " + this.statusText)
			}
		}

	}

	function searchUserName(input) {

		if (input.value.length > 2) {
			var request = new ajaxRequest()

			request.open("GET", "leilaservice.php?username=" + input.value, true)
			request.send(null)

			request.onreadystatechange = function()
			{
				if (this.readyState == 4)
				{
					if (this.status == 200)
					{
						if (this.responseText != null)
						{
							var objectlist = JSON.parse(this.responseText)
							document.getElementById('usersearchbox').innerHTML = ""
							document.getElementById('usersearchbox').style.display = "block"
							for (x in objectlist) {
								document.getElementById('usersearchbox').innerHTML += "<div onclick=\"setUserId(" + objectlist[x].id + ")\">ID: " + objectlist[x].id + " - " + objectlist[x].name + '</div>'
							}
						}
						else alert("Ajax error: No data received")
					}
					else alert( "Ajax error: " + this.statusText)
				}
			}
		}	else {
			document.getElementById('usersearchbox').innerHTML = ""
			document.getElementById('usersearchbox').style.display = "none"
		}
	}

	function setUserId(id) {
		document.getElementById('owner').value = id
		document.getElementById('usersearchbox').style.display = "none"
		updateNames()
	}

	function ajaxRequest()
	{
		try
		{
			var request = new XMLHttpRequest()
		}
		catch(e1)
		{
			try
			{
				request = new ActiveXObject("Msxml2.XMLHTTP")
			}
			catch(e2)
			{
				try
				{
					request = new ActiveXObject("Microsoft.XMLHTTP")
				}
				catch(e3)
				{
					request = false
				}
			}
		}
		return request
	}

	function unescapeHtml(unsafe) {
		return unsafe
			.replace(/&amp;/g, "&")
			.replace(/&ouml;/g, "ö")
			.replace(/&Ouml;/g, "Ö")
			.replace(/&auml;/g, "ä")
			.replace(/&Auml;/g, "Ä")
			.replace(/&uuml;/g, "ü")
			.replace(/&Uuml;/g, "Ü")
			.replace(/&szlig;/g, "ß")
			.replace(/&lt;/g, "<")
			.replace(/&gt;/g, ">")
			.replace(/&quot;/g, "\"")
			.replace(/&#039;/g, "'");
	}
</script>

</body>
</html>