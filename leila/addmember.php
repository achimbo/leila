<?php
?>

<!DOCTYPE html>
<html>
<head>
   <link rel="stylesheet" href="leila.css" type="text/css">
</head>
<body>
<?php include 'menu.php';?>
<div id="content">

<h1>Add member</h1>

<?php 
	if (isset($error) && $error != "") echo "<div class='errorclass'>Fehler: $error";
	isset($message) ? $message : ''
?>
<h1>Objekt hinzuf&uuml;gen</h1>
<form method="post" action="addmember.php"  enctype="multipart/form-data">
Vorname <input type="text" name="firstname" value="<?php if (isset($_POST['firstname'])) echo $_POST['name'];?> "><br>
Nachname <input type="text" name="lastname" value="<?php if (isset($_POST['lastname'])) echo $_POST['name'];?> "><br>

<input type="submit" name="addmember" value="Mitglied anlegen">

</form>
</div>
</body>
</html>