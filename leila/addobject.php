<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$created = false;

if (isset($_POST['name']) && !isset($_POST['getsubcategories'])) {
	$error = isempty($_POST['name'], "Name");
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
	$shelf = sanitizeMySQL($connection, $_POST['shelf']);

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
	$shelf != '' ? $shelf = addquotes($shelf) : $shelf = 'NULL';
	
	
	$query = "INSERT INTO objects (name, description, shelf, image, imagename, imagetype, scaledimage, dateadded, internalcomment, owner, loaneduntil, isavailable) 
		VALUES ('$name', $description, $shelf, $image, $imagename, $imagetype, $imagescaled, $dateadded, $internalcomment, $owner, $loaneduntil, '1')" ;
//	echo "Query ist " . $query;
	$result = $connection->query($query);
	if (!$result) { 
		die ("Angaben fehlerhaft, Objekt nicht erstellt " . $connection->error);
		$message = '<div class="errorclass">Fehler, Objekt nicht erstellt</div>';
	} else {
		if (isset($_POST['subcategory'])) {$cat = sanitizeMySQL($connection, $_POST['subcategory']); } 
			else {$cat = sanitizeMySQL($connection, $_POST['topcategory']);	}
		
		$insid = mysqli_insert_id($connection);
		$query = "INSERT INTO objects_has_categories (object_id, category_id) 
				VALUES ('$insid', '$cat' )";
//		echo "Query ist " . $query;
		$result = $connection->query($query);
		if (!$result) {
			die ("Angaben fehlerhaft, Kategorie nicht erstellt " . $connection->error);
			$message = '<div class="errorclass">Fehler, Kategorie nicht erstellt</div>';
		} 
			else {
				$message = '<div class="message"><a href="editobject.php?ID=' .$insid . '"> Objekt</a> erstellt <br> <a href="printlabel.php?ID=' .$insid . '"> Label drucken</a> </div>';
				$created = true;
			}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Objekt hinzuf&uuml;gen</title>
	<link rel="stylesheet" href="leila.css" type="text/css">
	<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">
</head>
<script src="jquery/jquery.js"></script>
<script src="jquery-ui/jquery-ui.min.js"></script>

<body>
<?php include 'menu.php';?>
<div id='content'>
<?php if (isset($error) && $error != "") echo "<div class='errorclass'>Fehler: $error </div>";
echo isset($message) ? $message : ''?>
<h1>Objekt hinzuf&uuml;gen</h1>
<form method="post" action="addobject.php"  enctype="multipart/form-data">
<!-- hidden submit, so that enter button in name field works, else "getsubcategories" would be default -->
<input type="submit" value="hs" style="visibility: hidden;" /><br>
<label for="name">Name</label> <input id="name" type="text" name="name" Name ="name" autofocus="autofocus" value="<?php 
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
<label for="description">Beschreibung</label> <textarea id="description" name ="description" rows="5" cols="20"><?php if(isset($_POST['description']) && !$created){ echo $_POST['description']; } ?></textarea> <br>
<label for="shelf">Regal</label> <input id="shelf" type="text" name="shelf" value="<?php if(isset($_POST['shelf']) && !$created){ echo $_POST['shelf']; } ?>"><br>
<label for="image">Foto</label> <input id="image" type="file" name="image"> <br>
<label for="dateadded">Eingangsdatum</label> <input id="dateadded" type="text" name="dateadded" value="<?= getcurrentdate()?>"> <br>
<label for="internalcomment">Interner Kommentar</label> <textarea id="internalcomment" name ="internalcomment" rows="5" cols="20"><?php if(isset($_POST['internalcomment']) && !$created){ echo $_POST['internalcomment']; } ?>
</textarea> <br>
<label for="owner">Eigent&uuml;mer ID &#x1f50e;</label> <input id="owner" type="text" name="owner" oninput="displayUserName(this)" value="<?php if(isset($_POST['owner']) && !$created){ echo $_POST['owner']; } ?>"> <br>
<label for="username">Eigent. Name &#x1f50e;</label>
<input type="text" name="username" id="username" oninput="searchUserName(this)"><br>
<div id="usersearchbox"></div>
<label for="loaneduntil">Geliehen bis &#x1f4c5;</label> <input id="loaneduntil" type="text" name="loaneduntil" value="<?php if(isset($_POST['loaneduntil']) && !$created){ echo $_POST['loaneduntil']; } ?>"> <br>
	<script type="text/javascript">
		$( "#loaneduntil" ).datepicker({
			  dateFormat: "yy-mm-dd",
				  firstDay: 1
		});
	</script>

<input type="submit" name="addobject" value="Objekt anlegen">
</form>
</div>
</body>
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

</html>
