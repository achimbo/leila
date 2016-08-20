<?php

require_once 'variables.php';
require_once 'tools.php';

session_start();
require_once('configlocale.php');

// comment this line out for install, add admin user, then uncomment immediatly!
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die (_("please <a href='login.php'>login</a>"));

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

	if($usertype == 1){
		$error .= isempty($email, "Email");
	}

	if ($usertype == 1 && !passwordvalid($password) ) {
		$error .= _("password needs to have 6 characters and a special character");
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
			die (_("data invalid, member not created") . $connection->error);
			$message = '<div class="errorclass">' . _("error, member not created") . '</div>';
		} else {
			$insid = mysqli_insert_id($connection);
			$message = '<div class="message"><a href="editmember.php?ID=' .$insid . '">' . _("member") . '</a> ' . _("created") . '<br> <a href="printmember.php?ID=' .$insid . '">' . _("print member form") . '</a> </div>';
			$created = true;
			if ($email != "") {
				$subject = _("LOT rules");
				$headers = "From: $fromemail\r\n";
				$headers .= "Mime-Version: 1.0\r\n";
				$headers .= "Content-type: text/plain; charset=utf-8\r\n";
				$mailbody = sprintf(_('hello %1$s, \n INSERT RULES \n kind regards LOT'), $firstname);

				if (mail($email, $subject, $mailbody, $headers)) {
					$message .= _("sent email");
				}
			}
		}
	}

}

?>

<!DOCTYPE html>
<html>

<body>
<head>
	<link rel="stylesheet" href="leila-new.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css" type="text/css">
	<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">
	<script src="jquery/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="jquery-ui/jquery-ui.min.js"></script>



	<meta charset="utf-8"/>
	<title><?= _('add member')?></title>
</head>

<body>
<div class="container">

	<?php include 'nav.php';?>

	<script type="text/javascript">
		document.getElementById('memberstab').className = 'active';
		document.getElementById('objectspane').className = 'tab-pane';
		document.getElementById('memberspane').className = 'tab-pane active';
	</script>

	<h1><?= _('add member')?></h1>

	<div class="row margin-top">
		<div class="col-md-6">
			<?php
			if (isset ( $error ) && $error != "")
				echo "<div class='alert alert-danger'>" . _('error') . ":" . $error . " </div><p>";
			if (isset ( $message ) && $message != "") {
				echo "<div class='alert alert-success'>" . $message . " </div><p>";
			}
			?>

			<form method="post" action="addmember.php"  enctype="multipart/form-data">
				<div class="form-group">
					<label for="usertype"><?= _('account type')?></label>
					<select name="usertype" class="form-control" id="usertype" size="1">
						<option value="1"><?= _('administrator')?></option>
						<option value="2" selected="selected"><?= _('user')?></option>
						<option value="3"><?= _('donor')?></option>
					</select>
				</div>
				<div class="form-group">
					<label for="firstname"><?= _('first name')?></label>
					<input type="text" class="form-control" name="firstname" id="firstname" autofocus="autofocus" value="<?php if (isset($_POST['firstname']) && !$created) echo $_POST['firstname'];?>">
				</div>
				<div class="form-group">
					<label for="lastname"><?= _('last name')?></label>
					<input type="text" class="form-control" name="lastname" id="lastname" value="<?php if (isset($_POST['lastname']) && !$created) echo $_POST['lastname'];?>">
				</div>
				<div class="form-group">
					<label for="password"><?= _('password')?></label>
					<input type="password" class="form-control" name="password" id="password">
				</div>
				<div class="form-group">
					<label for="street"><?= _('street')?></label>
					<input type="text" class="form-control" name="street" id="street" value="<?php if (isset($_POST['street']) && !$created) echo $_POST['street'];?>">
				</div>
				<div class="form-group">
					<label for="city"><?= _('city')?></label>
					<input type="text" class="form-control" name="city" id="city" value="<?php if (isset($_POST['city']) && !$created) echo $_POST['city'];?>">
				</div>
				<div class="form-group">
					<label for="zipcode"><?= _('po code')?></label>
					<input type="text" class="form-control" name="zipcode" id="zipcode" value="<?php if (isset($_POST['zipcode']) && !$created) echo $_POST['zipcode'];?>">
				</div>
				<div class="form-group">
					<label for="country"><?= _('country')?></label>
					<input type="text" class="form-control" name="country" id="country" value="<?php if (isset($_POST['country']) && !$created) {echo $_POST['country'];} else {echo "&Ouml;sterreich";}?>">
				</div>
				<div class="form-group">
					<label for="telephone"><?= _('telephone number')?></label>
					<input type="text" class="form-control" name="telephone" id="telephone" value="<?php if (isset($_POST['telephone']) && !$created) echo $_POST['telephone'];?>">
				</div>
				<div class="form-group">
					<label for="email"><?= _('email')?></label>
					<input type="text" class="form-control" name="email" id="email" value="<?php if (isset($_POST['email']) && !$created) echo $_POST['email'];?>">
				</div>
				<div class="form-group">
					<label for="getsnewsletter"><?= _('receive newsletter')?></label>
					<input type="checkbox" name="getsnewsletter" id="getsnewsletter" value=1 <?php if (!isset($_POST['addmember'])) {echo "checked='checked'";} elseif ((isset($_POST['getsnewsletter']) && !$created) || $created) {echo "checked='checked'";} ?>>
				</div>
				<div class="form-group">
					<label for="idnumber"><?= _('id number')?></label>
					<input type="text" class="form-control" name="idnumber" id="idnumber" value="<?php if (isset($_POST['idnumber']) && !$created) echo $_POST['idnumber'];?>">
				</div>
				<div class="form-group">
					<label for="comment"><?= _('comment')?></label>
					<textarea name ="comment" class="form-control" id="comment" rows="5" cols="20"><?php if(isset($_POST['comment']) && !$created) echo $_POST['comment'];?></textarea>
				</div>
				<div class="form-group">
					<label for="comember"><?= _('co user')?></label>
					<input type="text" class="form-control" name="comember" id="comember" value="<?php if (isset($_POST['comember']) && !$created) echo $_POST['comember'];?>">
				</div>

				<input type="submit" class="btn btn-default" name="addmember" value="<?= _('create member')?>">

			</form>
			<p>
		</div>
	</div

</div>
</body>
</html>