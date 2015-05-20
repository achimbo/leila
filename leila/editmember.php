<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

if (isset ( $_GET ['ID'] )) {
	$id = sanitizeMySQL ( $connection, $_GET ['ID'] );
} else {
	die ( "missing ID" );
}

if (isset($_POST['addfee'])) {
		$fromfee = sanitizeMySQL ( $connection, $_POST ['fromfee'] );
		$untilfee = sanitizeMySQL ( $connection, $_POST ['untilfee'] );
		$amount = sanitizeMySQL ($connection, $_POST ['amount']);
		
		$error = datepresent($fromfee);
		$error .= datepresent($untilfee);
		$error .= isint($amount);
		
		if ($error == "") {
			$query = "INSERT INTO membershipfees (`users_ID`, `from`, `until`, `amount`) VALUES ('$id', '$fromfee', '$untilfee', '$amount')";
			$result = $connection->query ( $query );
			if (! $result) {
				die ( "Mitgliedsbeitrag bereits vorhanden oder Datum falsch " . $connection->error );
			} else {
				$message = '<div class="message">Beitrag hinzugef&uuml;gt</div>';
			}
		}
}

if (isset ( $_POST ['deletemember'] )) {
	$query = "DELETE FROM users WHERE ID = $id";
	$result = $connection->query ( $query );
	if (! $result) die ( "Database query error" . $connection->error );
	echo '<head> <link rel="stylesheet" href="leila.css" type="text/css"></head>';
	include "menu.php";
	die ( "<div id='content'><h3>Member gel&ouml;scht </h3></div>" );
}

if (isset ( $_POST ['savemember'] )) {
	$firstname = sanitizeMySQL ( $connection, $_POST ['firstname'] );
	$lastname = sanitizeMySQL ( $connection, $_POST ['lastname'] );
	$usertype = sanitizeMySQL ( $connection, $_POST ['usertype'] );
	$password = sanitizeMySQL ( $connection, $_POST ['password'] );
	$street = sanitizeMySQL ( $connection, $_POST ['street'] );
	$city = sanitizeMySQL ( $connection, $_POST ['city'] );
	$zipcode = sanitizeMySQL ( $connection, $_POST ['zipcode'] );
	$country = sanitizeMySQL ( $connection, $_POST ['country'] );
	$telephone = sanitizeMySQL ( $connection, $_POST ['telephone'] );
	$email = sanitizeMySQL ( $connection, $_POST ['email'] );
	$idnumber = sanitizeMySQL ( $connection, $_POST ['idnumber'] );
	$comment = sanitizeMySQL ( $connection, $_POST ['comment'] );
	$comember = sanitizeMySQL ( $connection, $_POST ['comember'] );
	
	$error = isempty ( $firstname, "Vorname" );
	$error .= isempty ( $lastname, "Nachname" );
	$error .= isempty ( $street, "Straße" );
	$error .= isempty ( $city, "Stadt" );
	$error .= isempty ( $zipcode, "PLZ" );
	$error .= isempty ( $country, "Land" );
	
	if ($usertype == 1 && isset ( $_POST ['updatepassword'] ) && ! passwordvalid ( $password )) {
		$error .= "Passwort muss mindestens 6 Zeichen und Sonderzeichen haben";
	} elseif ($usertype == 1 && isset ( $_POST ['updatepassword'] ) && passwordvalid ( $password )) {
		$password = hash ( 'ripemd160', "$salt$password" );
		if ($error == "") {
			$query = "UPDATE users SET usertype = $usertype, password = '$password', firstname = '$firstname', lastname = '$lastname',
			street = '$street', city = '$city', zipcode = '$zipcode', country = '$country', telephone = '$telephone',
			email = '$email', idnumber = '$idnumber', comment = '$comment', comember = '$comember' WHERE ID = $id";
			$result = $connection->query ( $query );
			if (! $result) {
				die ( "Angaben fehlerhaft, nicht gespeichert " . $connection->error );
				$message = '<div class="errorclass">Fehler, nicht gespeichert</div>';
			} else {
				$message = '<div class="message">Member gespeichert</div>';
			}
		}
	} else {
		if ($error == "") {
			$query = "UPDATE users SET usertype = $usertype, firstname = '$firstname', lastname = '$lastname', 
		street = '$street', city = '$city', zipcode = '$zipcode', country = '$country', telephone = '$telephone', 
		email = '$email', idnumber = '$idnumber', comment = '$comment', comember = '$comember' WHERE ID = $id";
			$result = $connection->query ( $query );
			if (! $result) {
				die ( "Angaben fehlerhaft, nicht gespeichert " . $connection->error );
				$message = '<div class="errorclass">Fehler, nicht gespeichert</div>';
			} else {
				$message = '<div class="message">Member gespeichert</div>';
			}
		}
	}
}

$query = "SELECT * FROM users WHERE ID = " . $id;
$result = $connection->query ( $query );

if (! $result)
	die ( "Database query error" . $connection->error );

$result->data_seek ( 0 );
$row = $result->fetch_array ( MYSQLI_ASSOC );

?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="leila.css" type="text/css">
<title>Member anzeigen</title>
</head>
<body>
<?php include 'menu.php';?>
<div id="content">
	<?php
	
	if (isset ( $error ) && $error != "")
		echo "<div class='errorclass'>Fehler: $error </div>";
	if (isset ( $message ))
		echo $message;
	?>
	<h1>member editieren</h1>

		<form method="post" action="editmember.php?ID=<?=$id?>">

			<label for="id">ID</label> <input disabled="disabled" name="id"
				id="id" type="text" value="<?= $row['ID']?>"> <br> <label
				for="usertype">Usertyp</label> <select name="usertype" id="usertype"
				size="1">
				<option value="1"
					<?php if ($row['usertype'] == 1) {echo "selected=\"selected\" ";}?>>Admin</option>
				<option value="2"
					<?php if ($row['usertype'] == 2) {echo "selected=\"selected\" ";}?>>Benutzer_in
				</option>
				<option value="3"
					<?php if ($row['usertype'] == 3) {echo "selected=\"selected\" ";}?>>Verleiher_in
				</option>
			</select><br> <label for="firstname">Vorname</label> <input
				type="text" name="firstname" id="firstname"
				value="<?= $row['firstname']?>"> <br> <label for="lastname">Nachname</label>
			<input type="text" name="lastname" id="lastname"
				value="<?= $row['lastname']?>"> <br> <label for="password">Passwort</label>
			<input type="password" name="password" id="password"> Passwort
			&auml;ndern <input type="checkbox" name="updatepassword"
				value="update"> <br> <label for="street">Straße</label> <input
				type="text" name="street" id="street" value="<?= $row['street']?>">
			<br> <label for="city">Stadt</label> <input type="text" name="city"
				id="city" value="<?= $row['city']?>"> <br> <label for="zipcode">PLZ</label>
			<input type="text" name="zipcode" id="zipcode"
				value="<?= $row['zipcode']?>"> <br> <label for="country">Land</label>
			<input type="text" name="country" id="country"
				value="<?= $row['country']?>"> <br> <label for="telephone">Telefon</label>
			<input type="text" name="telephone" id="telephone"
				value="<?= $row['telephone']?>"> <br> <label for="email">Email</label>
			<input type="text" name="email" id="email"
				value="<?= $row['email']?>"> <br> <label for="idnumber">Ausweis
				Nummer</label> <input type="text" name="idnumber" id="idnumber"
				value="<?= $row['idnumber']?>"> <br> <label for="comment">Kommentar</label>
			<textarea name="comment" id="comment" rows="5" cols="20"><?=$row['comment']?></textarea>
			<br> <label for="comember">Co Member</label> <input type="text"
				name="comember" id="comember" value="<?=$row['comember']?>"> <br> <input
				type="submit" name="savemember" value="&Auml;nderungen speichern"><br>
			<input type="submit" name="deletemember" value="Member l&ouml;schen"
				onclick="return confirm('Sicher l&ouml;schen?');">
		</form>
		<div id="feelist">
			<br><br>
			
			<?php 
			$fees = getfees($id);
			echo "<table>";
			switch (isvaliduser($id)) {
				case -1:
				echo "<caption><div class='invalid'>Kein User</span></caption>";
				break;
				
				case 0:
				echo "<caption><div class='invalid'>Kein Mitgliedsbeitrag</span></caption>";
				break;

				case 1:
				echo "<caption><div class='tempvalid'>Mitgliedschaft l&auml;ft aus</span></caption>";
				break;
				
				case 2:
				echo "<caption><div class='valid'>Mitgliedsbeitr&auml;ge gezahlt</span></caption>";
				break;
			}			
			echo "<thead><tr><th>Von</th><th>Bis</th><th>Betrag</th></thead>";
			foreach ($fees as $fee) {
				echo "<tr><td>" . $fee['from'] . "</td><td>" . $fee['until'] . "</td><td>" . $fee['amount'] . "</td></tr>" ;
			}
			echo "</table><br>";
			?>
			
			<form method="post" action="editmember.php?ID=<?=$id?>">
				<label for="fromfee">Beitrag ab</label> 
				<input type="text" name="fromfee" id="fromfee" value="<?= getcurrentdate()?>"> <br>
				<label for="untilfee">Beitrag bis</label>
				<input type="text" name="untilfee" id="untilfee" value="<?= date("Y-m-d", (time() + 60 * 60 * 24 * 365))?>"> <br>
				<label for="amount">Beitragsh&ouml;he</label> 
				<input type="text" name="amount" id="amount"> <br>
				<input type="submit" name="addfee" value="Beitrag hinzuf&uuml;gen">
			</form>
		</div>
		<br> <a href="lendobject.php?userid=<?=$id?>">Ausleihen</a>

	</div>
</body>
</html>