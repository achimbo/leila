<?php

require_once 'variables.php';
require_once 'tools.php';

session_start();
// comment this line out for install, add admin user, then uncomment immediatly!
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$created = false;

if (isset($_POST['addmember'])) {
	
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($connection->connect_error) die($connection->connect_error);
	
	$firstname = sanitizeMySQL($connection, $_POST['firstname']);
	$lastname = sanitizeMySQL($connection, $_POST['lastname']);
	$usertype = sanitizeMySQL($connection, $_POST['usertype']);
	$password = sanitizeMySQL($connection, $_POST['password']);
	$street = sanitizeMySQL($connection, $_POST['street']);
	$city = sanitizeMySQL($connection, $_POST['city']);
	$zipcode = sanitizeMySQL($connection, $_POST['zipcode']);
	$country = sanitizeMySQL($connection, $_POST['country']);
	$telephone = sanitizeMySQL($connection, $_POST['telephone']);
	$email = sanitizeMySQL($connection, $_POST['email']);
	$idnumber = sanitizeMySQL($connection, $_POST['idnumber']);
	$comment = sanitizeMySQL($connection, $_POST['comment']);
	$comember = sanitizeMySQL($connection, $_POST['comember']);
	
	$error = isempty($firstname, "Vorname");
	$error .= isempty($lastname, "Nachname");
	$error .= isempty($street, "Straße");
	$error .= isempty($city, "Stadt");
	$error .= isempty($zipcode, "PLZ");
	$error .= isempty($country, "Land");
	
	if ($usertype == 1 && !passwordvalid($password) ) {
		$error .= "Passwort muss mindestens 6 Zeichen und Sonderzeichen haben";
	} elseif ($usertype == 2 || $usertype == 3) {
		$password = "NULL";
	} else {
		$password = hash('ripemd160', "$salt$password");
		$password = addquotes($password);
	}
	

	if ($error == "") {
		$query = "INSERT INTO users (usertype, password, firstname, lastname, street, city, zipcode, country, telephone, email, idnumber, comment, comember )
		VALUES ($usertype, $password, '$firstname', '$lastname', '$street', '$city', '$zipcode', '$country', '$telephone', '$email', '$idnumber', '$comment', '$comember' )" ;
			// echo "Query ist " . $query;
		$result = $connection->query($query);
		if (!$result) {
			die ("Angaben fehlerhaft, Objekt nicht erstellt " . $connection->error);
			$message = '<div class="errorclass">Fehler, Objekt nicht erstellt</div>';
		} else {
			$insid = mysqli_insert_id($connection);
			$message = '<div class="message"><a href="editmember.php?ID=' .$insid . '"> Member</a> erstellt</div>';
			$created = true;
		}
	}
	
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Member hinzuf&uuml;gen</title>
	<link rel="stylesheet" href="leila.css" type="text/css">
</head>
<body>
<?php include 'menu.php';?>
<div id="content">

<h1>Mitglied hinzuf&uuml;gen</h1>

<?php 
	if (isset($error) && $error != "") echo "<div class='errorclass'>Fehler: $error </div>";
	if (isset($message)) echo $message ;?>
<form method="post" action="addmember.php"  enctype="multipart/form-data">
	<label for="usertyp">Account Typ</label>
	<select name="usertype" id="usertype" size="1">
		<option value="1">Admin</option>
		<option value="2" selected="selected">Benutzer_in</option>
		<option value="3">Verleiher_in</option>
	</select><br>
	<label for="firstname">Vorname</label>
	<input type="text" name="firstname" id="firstname" autofocus="autofocus" value="<?php if (isset($_POST['firstname']) && !$created) echo $_POST['firstname'];?>"><br>
	<label for="lastname">Nachname</label>
	<input type="text" name="lastname" id="lastname" value="<?php if (isset($_POST['lastname']) && !$created) echo $_POST['lastname'];?>"><br>
	<label for="password">Passwort</label>
	<input type="password" name="password" id="password"><br>
	<label for="street">Straße</label>
	<input type="text" name="street" id="street" value="<?php if (isset($_POST['street']) && !$created) echo $_POST['street'];?>"><br>
	<label for="city">Stadt</label>
	<input type="text" name="city" id="city" value="<?php if (isset($_POST['city']) && !$created) echo $_POST['city'];?>"><br>
	<label for="zipcode">Postleitzahl</label>
	<input type="text" name="zipcode" id="zipcode" value="<?php if (isset($_POST['zipcode']) && !$created) echo $_POST['zipcode'];?>"><br>
	<label for="country">Land</label>
	<input type="text" name="country" id="country" value="<?php if (isset($_POST['country']) && !$created) {echo $_POST['country'];} else {echo "&Ouml;sterreich";}?>"><br>
	<label for="telephone">Telefonnummer</label>
	<input type="text" name="telephone" id="telephone" value="<?php if (isset($_POST['telephone']) && !$created) echo $_POST['telephone'];?>"><br>
	<label for="email">Email</label>
	<input type="text" name="email" id="email" value="<?php if (isset($_POST['email']) && !$created) echo $_POST['email'];?>"><br>
	<label for="idnumber">Ausweisnummer</label>
	<input type="text" name="idnumber" id="idnumber" value="<?php if (isset($_POST['idnumber']) && !$created) echo $_POST['idnumber'];?>"><br>
	<label for="comment">Kommentar</label>
	<textarea name ="comment" id="comment" rows="5" cols="20"><?php if(isset($_POST['comment']) && !$created) echo $_POST['comment'];?></textarea> <br>	
	<label for="comember">Mitbenutzer_in</label>
	<input type="text" name="comember" id="comember" value="<?php if (isset($_POST['comember']) && !$created) echo $_POST['comember'];?>"><br>	
<input type="submit" name="addmember" value="Mitglied anlegen">

</form>
</div>
</body>
</html>