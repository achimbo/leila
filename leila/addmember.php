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
	if (isset($_POST['getsnewsletter']) ){
		$getsnewsletter = 1;	}
		else {
			$getsnewsletter = 0;
		}
	
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
		$query = "INSERT INTO users (usertype, password, firstname, lastname, street, city, zipcode, country, telephone, email, idnumber, comment, comember, getsnewsletter )
		VALUES ($usertype, $password, '$firstname', '$lastname', '$street', '$city', '$zipcode', '$country', '$telephone', '$email', '$idnumber', '$comment', '$comember', '$getsnewsletter' )" ;
			// echo "Query ist " . $query;
		$result = $connection->query($query);
		if (!$result) {
			die ("Angaben fehlerhaft, Objekt nicht erstellt " . $connection->error);
			$message = '<div class="errorclass">Fehler, Objekt nicht erstellt</div>';
		} else {
			$insid = mysqli_insert_id($connection);
			$message = '<div class="message"><a href="editmember.php?ID=' .$insid . '"> Member</a> erstellt <br> <a href="printmember.php?ID=' .$insid . '"> Mitgliedformular drucken</a> </div>';
			$created = true;
			if ($email != "") {
				$subject = "Leihladen Leihregeln";
				$headers = "From: $fromemail\r\n";
				$headers .= "Mime-Version: 1.0\r\n";
				$headers .= "Content-type: text/plain; charset=utf-8\r\n";
				$mailbody = <<<_END
Hallo {$firstname}
Anbei findest du die Leihregeln des Leihladens

Version 1.1 30. Juli 2012 beschlossen vom Leila Orga-Treff

commons = Selbstregulation   *   Leila = Mitmachladen hier mitregeln: http://pad.spline.de/cW5suQ7AAr

1 - So wirst du Mitglied

Um Neu-Mitglied zu werden fülle unser Formblatt aus und bringe einen Gegenstand oder mehr von dir in den Leihpool ein. Du kannst entscheiden, ob du diesen Gegenstand Leila schenkst oder ausleihst. Damit erhältst du eine namentliche Leila-Karte vergleichbar mit einer Bibliothekskarte. Die Leila-Karte ist nicht übertragbar. Die Leihe aller Gegenstände aus dem Pool ist für Mitglieder unentgeltlich (vgl. BGB § 598). Für die Mitgliedschaft wird eine freiwillige Spende an den Verein GeLa e.V. erbeten (siehe "Spenden"). Wenn du eine Sache in den Pool eingebracht hast, kannst du auch nur eine Sache ausleihen. Wenn du zwei Sachen eingebracht hast, kannst du zwei ausleihen und so weiter. Dies hängt nicht vom Wert oder der Größe der Gegenstände ab. Wir behalten uns vor, manches als zusammengehöriges Set und damit als einen Gegenstand zu definieren  (z.B. Besteck, Bauklötze u.ä. viel-teiliges).
2 - Ende der Mitgliedschaft
Als Mitglied kannst du deine Mitgliedschaft jederzeit kündigen. Zur Kündigung genügt eine entsprechende Erklärung in Textform an Leila.
3 - Pfand

Auf wertvolle Dinge wird beim Verleih Pfand erhoben. Für bewährte Mitglieder (nach Zahl der ordentlich und pünktlich zurückgebrachten Gegenstände) entfällt das Pfand.

4 - Fristen
Die Ausleihzeit hängt von der Sache ab und wird individuell, in Abwägung von persönlicher Nutzungsabsicht und allgemeinem Bedarf, vereinbart. Dinge sollen nach der Nutzung schnellstmöglich zurückgegeben werden. Eine kurze Ausleihzeit verstärkt, dass die Dinge für viele Mitglieder zugänglich sind. Du kannst die Leihe zweimal verlängern - vorausgesetzt, kein anderes  Mitglied hat die Sache vorgemerkt. Für die verspätete Rückgabe gibt es Entgelte (vgl. Entgeltkatalog).

5 - Rückgabe
Die Rückgabe ist nach Ablauf der Frist fällig (BGB § 604). Ausgeliehene Dinge sollen nach der Rückgabe so (sauber, ganz, vollständig) sein, wie sie bei der Verleihung waren, ist dies nicht der Fall fallen Entgelte an (vgl. Entgeltkatalog).
6 - Nutzung
Die Sachen des gemeinschaftlichen Pools sind nur für den privaten Gebrauch bestimmt. Die kommerzielle Bewirtschaftung von Gemeingütern ist untersagt (vgl. BGB § 603).
7 - Haftung/Schadensfall
Gegenüber den Mitgliedern haftet Leila nur für Vorsatz und grobe Fahrlässigkeit (vgl. BGB § 599).
8 - Reparatur

Im Fall der Reparatur trägst du als Mitglied die anfallenden Reparaturkosten bis zu 50 % des Pfands. Für gebrauchsüblichen Verschleiß fallen keine Reparaturkosten an (z.B. Abnutzung von Bremsklötzen am Fahrrad).
9 - Verlust

Verlierst du bei der Leihe die Leihsache wird das Pfand einbehalten oder du ersetzt den Gegenstand durch einen vergleichbaren (d.h. ggf. gebrauchten in ähnlicher Qualität).
						
				Liebe Grüße Leihladen Wien
_END;
					
				if (mail($email, $subject, $mailbody, $headers)) {
					$message .= "Email versand <p>";
				}
			}
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

	<label for="getsnewsletter">Newsletter empfangen</label>
	<input type="checkbox" name="getsnewsletter" id="getsnewsletter" value=1 <?php if (!isset($_POST['addmember'])) {echo "checked='checked'";} elseif ((isset($_POST['getsnewsletter']) && !$created) || $created) {echo "checked='checked'";} ?>><p>	
	
	
<input type="submit" name="addmember" value="Mitglied anlegen">

</form>
</div>
</body>
</html>